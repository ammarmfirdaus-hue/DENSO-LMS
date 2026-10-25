require('dotenv').config();

const bcrypt = require('bcryptjs');
const crypto = require('node:crypto');
const env = require('../src/config/env');
const { sql, pool } = require('../src/config/database');

const demoPassword = process.env.SEED_DEMO_PASSWORD || '';
if (process.env.SEED_DEMO !== 'true') {
  console.error('Refusing to seed: set SEED_DEMO=true for this local demo-data command.');
  process.exit(1);
}
if (/(^|[_-])(prod|production|live)([_-]|$)/i.test(env.DB_NAME)) {
  console.error(`Refusing to seed production-like database '${env.DB_NAME}'. Use a separate staging database.`);
  process.exit(1);
}
if (demoPassword.length < 12 || Buffer.byteLength(demoPassword, 'utf8') > 72) {
  console.error('Set SEED_DEMO_PASSWORD to a demo-only password with 12-72 UTF-8 bytes.');
  process.exit(1);
}

const staff = [
  { npk: 'DLS-DEMO-ADM-001', name: 'Dimas Demo Admin', email: 'admin.demo@example.test', role: 'admin' },
  ...Array.from({ length: 6 }, (_, i) => ({
    npk: `DLS-DEMO-TCH-${String(i + 1).padStart(3, '0')}`,
    name: ['Tara Demo Teacher', 'Raka Demo Instructor', 'Maya Demo Trainer', 'Budi Demo Coach', 'Nina Demo Mentor', 'Fajar Demo Tutor'][i],
    email: `teacher${i + 1}.demo@example.test`, role: 'teacher'
  })),
  ...Array.from({ length: 3 }, (_, i) => ({
    npk: `DLS-DEMO-REV-${String(i + 1).padStart(3, '0')}`,
    name: ['Rini Demo Reviewer', 'Agus Demo Reviewer', 'Sinta Demo Reviewer'][i],
    email: `reviewer${i + 1}.demo@example.test`, role: 'reviewer', status: i === 2 ? 'inactive' : 'active'
  })),
  ...Array.from({ length: 94 }, (_, i) => ({
    npk: `DLS-DEMO-TCH-${String(i + 7).padStart(3, '0')}`,
    name: `Demo Instructor ${String(i + 7).padStart(3, '0')}`,
    email: `teacher${i + 7}.demo@example.test`, role: 'teacher'
  })),
  ...Array.from({ length: 97 }, (_, i) => ({
    npk: `DLS-DEMO-REV-${String(i + 4).padStart(3, '0')}`,
    name: `Demo Reviewer ${String(i + 4).padStart(3, '0')}`,
    email: `reviewer${i + 4}.demo@example.test`, role: 'reviewer', status: i % 13 === 12 ? 'inactive' : 'active'
  }))
];

const courses = [
  {
    code: 'DLS-DEMO-SAFE-001', title: 'Keselamatan Kerja Manufaktur', category: 'Wajib',
    description: 'Data demo: APD, identifikasi bahaya, LOTO, dan prosedur tanggap darurat.',
    sessions: [
      { title: 'Pengenalan K3 dan APD', materials: [
        { title: 'Panduan APD area produksi', type: 'text', content: 'Kenali APD wajib, cara inspeksi, dan aturan penggunaan sebelum memasuki area produksi.' },
        { title: 'Video briefing keselamatan', type: 'video', content: 'https://example.test/demo/safety-briefing' }
      ] },
      { title: 'Identifikasi bahaya dan LOTO', materials: [
        { title: 'Checklist LOTO dasar', type: 'pdf', content: 'Materi demo. Ganti dengan dokumen pelatihan yang telah disetujui.' },
        { title: 'Latihan identifikasi bahaya', type: 'text', content: 'Catat bahaya, nilai risiko, dan tindakan pengendalian yang sesuai.' }
      ] }
    ]
  },
  {
    code: 'DLS-DEMO-QUAL-002', title: 'Dasar Quality Control dan 5S', category: 'Wajib',
    description: 'Data demo: standar kualitas, pengendalian proses, dan penerapan 5S.',
    sessions: [
      { title: 'Standar kualitas proses', materials: [
        { title: 'Pemeriksaan kualitas awal', type: 'text', content: 'Periksa spesifikasi, alat ukur, dan kondisi proses sebelum produksi dimulai.' },
        { title: 'Referensi standar kualitas', type: 'link', content: 'https://example.test/demo/quality-standard' }
      ] },
      { title: 'Praktik 5S', materials: [
        { title: 'Lembar audit 5S', type: 'pdf', content: 'Materi demo untuk latihan audit area kerja.' },
        { title: 'Contoh visual 5S', type: 'image', content: 'Gunakan gambar demo yang sudah disetujui tim.' }
      ] }
    ]
  },
  {
    code: 'DLS-DEMO-LEAN-003', title: 'Lean Manufacturing dan Kaizen', category: 'Pilihan',
    description: 'Data demo: identifikasi pemborosan, pemecahan masalah, dan perbaikan berkelanjutan.',
    sessions: [
      { title: 'Mengenali pemborosan', materials: [
        { title: 'Delapan pemborosan proses', type: 'text', content: 'Pelajari contoh pemborosan dan cara mengamati aliran kerja.' },
        { title: 'Studi kasus Kaizen', type: 'video', content: 'https://example.test/demo/kaizen-case' }
      ] },
      { title: 'Siklus PDCA', materials: [
        { title: 'Template rencana PDCA', type: 'pdf', content: 'Template latihan PDCA untuk data dummy.' },
        { title: 'Sumber belajar Kaizen', type: 'link', content: 'https://example.test/demo/kaizen-resources' }
      ] }
    ]
  }
];

const demoSubjects = [
  'keselamatan kerja dan identifikasi risiko', 'quality control dan audit proses', 'lean manufacturing dan Kaizen',
  'otomasi industri dan sensor', 'lingkungan kerja dan pengelolaan limbah', 'komunikasi dan kepemimpinan tim',
  'perawatan mesin dan pencatatan gangguan', 'ergonomi dan kesehatan kerja', 'analisis data produksi dan pemecahan masalah',
  'standar kerja, dokumentasi, dan perbaikan berkelanjutan'
];
const courseTopics = [
  ['DLS-DEMO-PLC-004', 'Pengenalan Otomasi Industri', 'Pilihan', 'sensor, PLC, interlock keselamatan, dan otomasi dasar'],
  ['DLS-DEMO-ENVI-005', 'Pengelolaan Lingkungan Kerja', 'Wajib', 'pemilahan limbah, efisiensi energi, dan praktik lingkungan'],
  ['DLS-DEMO-LEAD-006', 'Komunikasi dan Kepemimpinan Tim', 'Pilihan', 'komunikasi kerja, coaching, dan koordinasi tim'],
  ['DLS-DEMO-MAINT-007', 'Perawatan Mesin Dasar', 'Wajib', 'inspeksi mesin, autonomous maintenance, dan pencatatan gangguan'],
  ['DLS-DEMO-ERG-008', 'Ergonomi dan Kesehatan Kerja', 'Wajib', 'postur kerja, ergonomi, dan pencegahan kelelahan'],
  ...Array.from({ length: 92 }, (_, index) => {
  const number = index + 9;
  const subject = demoSubjects[index % demoSubjects.length];
  const title = `Modul Operasional ${String(number).padStart(3, '0')}`;
  return [`DLS-DEMO-OPS-${String(number).padStart(3, '0')}`, title, number % 3 === 0 ? 'Pilihan' : 'Wajib', subject];
  })
];
for (const [code, title, category, subject] of courseTopics) {
  courses.push({ code, title, category, description: `Data demo untuk uji alur produksi: ${subject}.`, sessions: [
    { title: `Dasar ${title}`, materials: [
      { title: `Panduan ${title}`, type: 'text', content: `Materi simulasi tentang ${subject}. Tidak berisi prosedur operasional resmi.` },
      { title: `Video pengantar ${title}`, type: 'video', content: `https://example.test/demo/${code.toLowerCase()}/intro` }
    ] },
    { title: `Praktik ${title}`, materials: [
      { title: `Checklist ${title}`, type: 'pdf', content: `Lembar latihan simulasi ${subject}.` },
      { title: `Studi kasus ${title}`, type: 'text', content: `Diskusikan penerapan ${subject} pada skenario latihan.` }
    ] }
  ] });
}

const demoLearners = Array.from({ length: 100 }, (_, i) => ({
  npk: `DLS-DEMO-STU-${String(i + 1).padStart(3, '0')}`,
  email: [
    'dina.demo@example.test', 'bima.demo@example.test', 'sari.demo@example.test', 'rizky.demo@example.test',
    'nadia.demo@example.test', 'ilham.demo@example.test', 'putri.demo@example.test', 'arif.demo@example.test',
    'lina.demo@example.test', 'yoga.demo@example.test', 'dewi.demo@example.test', 'rian.demo@example.test',
    'mira.demo@example.test', 'doni.demo@example.test', 'ayu.demo@example.test', 'eka.demo@example.test',
    'tono.demo@example.test', 'wulan.demo@example.test', 'bayu.demo@example.test', 'intan.demo@example.test',
    'rudi.demo@example.test', 'citra.demo@example.test', 'fikri.demo@example.test', 'nia.demo@example.test',
    'andi.demo@example.test', 'salsa.demo@example.test', 'hendra.demo@example.test', 'meli.demo@example.test',
    'dika.demo@example.test', 'tari.demo@example.test'
  ][i] || `student${String(i + 1).padStart(3, '0')}.demo@example.test`,
  name: ['Dina Demo Operator', 'Bima Demo Quality', 'Sari Demo Engineering', 'Rizky Demo Produksi', 'Nadia Demo Logistik', 'Ilham Demo Teknisi', 'Putri Demo QA', 'Arif Demo Line Leader', 'Lina Demo Planning', 'Yoga Demo Maintenance', 'Dewi Demo Safety', 'Rian Demo Operator', 'Mira Demo Engineer', 'Doni Demo Quality', 'Ayu Demo Production', 'Eka Demo Trainer', 'Tono Demo Warehouse', 'Wulan Demo Analyst', 'Bayu Demo Technician', 'Intan Demo Staff', 'Rudi Demo Operator', 'Citra Demo QA', 'Fikri Demo Engineer', 'Nia Demo Safety', 'Andi Demo Production', 'Salsa Demo Planner', 'Hendra Demo Maintenance', 'Meli Demo Quality', 'Dika Demo Warehouse', 'Tari Demo Analyst'][i] || `Peserta Demo ${String(i + 1).padStart(3, '0')}`,
  code: courses[(i * 3) % courses.length].code,
  progress: [0, 25, 50, 75, 100][i % 5],
  status: i % 5 === 4 ? 'completed' : 'active'
}));
const learners = demoLearners.flatMap((learner, i) => Array.from({ length: 6 }, (_, slot) => {
  const courseIndex = (i * 13 + slot * 17) % courses.length;
  const course = courses[courseIndex];
  const progress = [0, 25, 50, 75, 100][(i * 7 + courseIndex * 3 + slot) % 5];
  const status = progress === 100 ? 'completed' : ((i + courseIndex) % 7 === 1 ? 'dropped' : 'active');
  return { ...learner, seedIndex: i, courseIndex, code: course.code, progress, status };
}));
// Retain the three original starter-course pairs from the previous small demo fixture.
for (const [studentIndex, courseIndex, progress] of [[0, 0, 100], [1, 2, 25], [2, 1, 100]]) {
  if (!learners.some(row => row.seedIndex === studentIndex && row.courseIndex === courseIndex)) {
    learners.push({ ...demoLearners[studentIndex], seedIndex: studentIndex, courseIndex, code: courses[courseIndex].code,
      progress, status: progress === 100 ? 'completed' : 'active' });
  }
}

async function main() {
  const passwordHash = await bcrypt.hash(demoPassword, 12);
  await pool.connect();
  const transaction = new sql.Transaction(pool);
  await transaction.begin();

  try {
    for (const account of staff) {
      await transaction.request()
        .input('npk', sql.VarChar(50), account.npk)
        .input('name', sql.NVarChar(150), account.name)
        .input('email', sql.NVarChar(254), account.email)
        .input('hash', sql.VarChar(255), passwordHash)
        .input('role', sql.VarChar(20), account.role)
        .input('status', sql.VarChar(20), account.status || 'active')
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.employee_accounts WHERE employee_npk=@npk)
          INSERT INTO dbo.employee_accounts(employee_npk,fullname,email,password_hash,role,status)
          VALUES(@npk,@name,@email,@hash,@role,@status);`);
    }

    const sessionIdByCodeAndTitle = new Map();
    const materialIdByCodeAndTitle = new Map();
    for (const [courseIndex, course] of courses.entries()) {
      const assignedTeacher = staff[1 + (courseIndex % 6)];
      await transaction.request()
        .input('code', sql.VarChar(30), course.code)
        .input('title', sql.NVarChar(200), course.title)
        .input('description', sql.NVarChar(4000), course.description)
        .input('category', sql.VarChar(20), course.category)
        .input('teacher', sql.VarChar(50), assignedTeacher.npk)
        .query(`IF EXISTS (SELECT 1 FROM dbo.courses WHERE course_code=@code)
            UPDATE dbo.courses SET title=@title,description=@description,category=@category,status='active',updated_by=@teacher,assigned_trainer_npk=@teacher,updated_at=SYSUTCDATETIME()
            WHERE course_code=@code;
          ELSE
            INSERT INTO dbo.courses(course_code,title,description,category,status,created_by,updated_by,assigned_trainer_npk)
            VALUES(@code,@title,@description,@category,'active',@teacher,@teacher,@teacher);`);

      const courseResult = await transaction.request().input('code', sql.VarChar(30), course.code)
        .query('SELECT course_id FROM dbo.courses WHERE course_code=@code');
      const courseId = courseResult.recordset[0].course_id;

      for (let sessionIndex = 0; sessionIndex < course.sessions.length; sessionIndex += 1) {
        const session = course.sessions[sessionIndex];
        const sessionResult = await transaction.request()
          .input('courseId', sql.BigInt, courseId)
          .input('title', sql.NVarChar(200), session.title)
          .input('sortOrder', sql.Int, sessionIndex + 1)
          .query(`IF NOT EXISTS (SELECT 1 FROM dbo.course_sessions WHERE course_id=@courseId AND title=@title)
              INSERT INTO dbo.course_sessions(course_id,title,sort_order) VALUES(@courseId,@title,@sortOrder);
            SELECT session_id FROM dbo.course_sessions WHERE course_id=@courseId AND title=@title;`);
        const sessionId = sessionResult.recordset[0].session_id;
        sessionIdByCodeAndTitle.set(`${course.code}|${session.title}`, sessionId);

        for (let materialIndex = 0; materialIndex < session.materials.length; materialIndex += 1) {
          const material = session.materials[materialIndex];
          const materialResult = await transaction.request()
            .input('sessionId', sql.BigInt, sessionId)
            .input('title', sql.NVarChar(200), material.title)
            .input('type', sql.VarChar(20), material.type)
            .input('content', sql.NVarChar(sql.MAX), material.content)
            .input('sortOrder', sql.Int, materialIndex + 1)
            .query(`IF NOT EXISTS (SELECT 1 FROM dbo.course_materials WHERE session_id=@sessionId AND title=@title)
              INSERT INTO dbo.course_materials(session_id,title,material_type,content,sort_order)
              VALUES(@sessionId,@title,@type,@content,@sortOrder);
            SELECT material_id FROM dbo.course_materials WHERE session_id=@sessionId AND title=@title;`);
          materialIdByCodeAndTitle.set(`${course.code}|${material.title}`, materialResult.recordset[0].material_id);
        }
      }
    }

    const activities = [
      ['course_created', 'Data demo: membuat course keselamatan kerja.'],
      ['session_created', 'Data demo: menambahkan sesi pengenalan K3 dan APD.'],
      ['material_created', 'Data demo: menambahkan panduan APD area produksi.']
    ];
    for (let index = 0; index < 120; index += 1) {
      const types = ['course_created', 'material_created', 'material_submitted', 'grade_updated', 'notification_sent', 'user_status_changed'];
      activities.push([types[index % types.length], `Data demo #${String(index + 1).padStart(3, '0')}: aktivitas admin/teacher untuk pengujian audit.` , staff[1 + (index % 6)].npk]);
    }
    for (let index = 0; index < activities.length; index += 1) {
      const [type, description, npk = staff[1].npk] = activities[index];
      await transaction.request().input('npk', sql.VarChar(50), npk)
        .input('type', sql.VarChar(50), type).input('description', sql.NVarChar(500), description)
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.activity_logs WHERE employee_npk=@npk AND activity_type=@type AND description=@description)
          INSERT INTO dbo.activity_logs(employee_npk,activity_type,description) VALUES(@npk,@type,@description);`);
    }

    for (const learner of learners) {
      const updatedAt = new Date(Date.now() - (learners.indexOf(learner) + 1) * 3600000);
      const score = learner.progress >= 50 ? Math.min(100, 60 + ((learners.indexOf(learner) * 11) % 41)) : null;
      const quizScore = learner.status === 'completed' && learner.courseIndex !== 6 && learner.courseIndex !== 7
        ? (2 + ((learner.seedIndex + learner.courseIndex) % 4)) * 20 : null;
      await transaction.request().input('npk', sql.VarChar(50), learner.npk)
        .input('code', sql.VarChar(30), learner.code).input('progress', sql.TinyInt, learner.progress)
        .input('status', sql.VarChar(20), learner.status).input('updated', sql.DateTime2, updatedAt)
        .input('score', sql.Decimal(5, 2), score).input('quizScore', sql.Decimal(5, 2), quizScore)
        .input('grader', sql.VarChar(50), staff[1 + (learners.indexOf(learner) % 6)].npk)
        .input('feedback', sql.NVarChar(1000), score === null ? null : 'Catatan demo: lanjutkan latihan untuk menguatkan pemahaman.')
        .query(`IF EXISTS (SELECT 1 FROM dbo.student_progress_snapshots WHERE student_npk=@npk AND course_code=@code)
            UPDATE dbo.student_progress_snapshots SET progress=@progress,enrollment_status=@status,score=@score,quiz_score=@quizScore,
              grade_feedback=@feedback,graded_by=CASE WHEN @score IS NULL THEN NULL ELSE @grader END,
              graded_at=CASE WHEN @score IS NULL THEN NULL ELSE @updated END,source_updated_at=@updated,synced_at=SYSUTCDATETIME()
            WHERE student_npk=@npk AND course_code=@code;
          ELSE
            INSERT INTO dbo.student_progress_snapshots(student_npk,course_code,progress,enrollment_status,score,quiz_score,grade_feedback,graded_by,graded_at,source_updated_at)
            VALUES(@npk,@code,@progress,@status,@score,@quizScore,@feedback,CASE WHEN @score IS NULL THEN NULL ELSE @grader END,
              CASE WHEN @score IS NULL THEN NULL ELSE @updated END,@updated);`);
    }

    const forumThreads = demoLearners.map((learner, index) => ({
      npk: learner.npk,
      index,
      code: courses[index % courses.length].code,
      title: `Diskusi demo ${String(index + 1).padStart(2, '0')}: ${courses[index % courses.length].title}`,
      body: `Data dummy forum untuk latihan diskusi #${index + 1}. Bagikan pertanyaan, temuan, dan langkah tindak lanjut yang sesuai.`,
      status: index % 11 === 0 ? 'locked' : (index % 8 === 0 ? 'hidden' : 'visible')
    }));
    for (const thread of forumThreads) {
      const threadResult = await transaction.request().input('npk', sql.VarChar(50), thread.npk)
        .input('code', sql.VarChar(30), thread.code).input('title', sql.NVarChar(200), thread.title)
        .input('body', sql.NVarChar(sql.MAX), thread.body)
        .input('status', sql.VarChar(20), thread.status)
      .input('sessionCode', sql.VarChar(50), `${thread.code}-S01`)
        .input('authorName', sql.NVarChar(150), demoLearners.find(item => item.npk === thread.npk)?.name || 'Student Demo')
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.forum_threads WHERE author_npk=@npk AND course_code=@code AND title=@title)
          INSERT INTO dbo.forum_threads(author_npk,author_name,author_role,course_code,session_code,title,body,status)
          VALUES(@npk,@authorName,'student',@code,@sessionCode,@title,@body,@status);
          SELECT TOP (1) thread_id FROM dbo.forum_threads WHERE author_npk=@npk AND course_code=@code AND title=@title ORDER BY thread_id;`);
      const threadId = threadResult.recordset[0]?.thread_id;
      if (threadId) {
        for (let replyIndex = 0; replyIndex < 4; replyIndex += 1) {
          const isTeacherReply = replyIndex === 3;
          const author = isTeacherReply ? staff[1 + (thread.index % 6)] : demoLearners[(replyIndex + thread.index) % demoLearners.length];
          const replyBody = isTeacherReply
            ? `Tanggapan Teacher demo: diskusi ${thread.index + 1} sudah mencakup langkah tindak lanjut yang tepat.`
            : `Balasan Student demo ${replyIndex + 1}: saya akan menerapkan langkah ini dan mencatat hasilnya.`;
          await transaction.request().input('threadId', sql.BigInt, threadId)
            .input('npk', sql.VarChar(50), author.npk)
            .input('name', sql.NVarChar(150), author.name)
            .input('role', sql.VarChar(20), isTeacherReply ? 'teacher' : 'student')
            .input('body', sql.NVarChar(2000), replyBody)
            .query(`IF NOT EXISTS (SELECT 1 FROM dbo.forum_replies WHERE thread_id=@threadId AND author_npk=@npk AND body=@body)
              INSERT INTO dbo.forum_replies(thread_id,author_npk,author_name,author_role,body,status) VALUES(@threadId,@npk,@name,@role,@body,'active');`);
        }
        await transaction.request().input('target', sql.BigInt, threadId).input('npk', sql.VarChar(50), demoLearners[3].npk)
          .query(`IF NOT EXISTS (SELECT 1 FROM dbo.forum_likes WHERE target_type='thread' AND target_id=@target AND author_npk=@npk)
            INSERT INTO dbo.forum_likes(target_type,target_id,author_npk) VALUES('thread',@target,@npk);`);
      }
    }

    for (const course of courses) {
      const assignedTeacher = staff[1 + (courses.indexOf(course) % 6)];
      for (const [sessionIndex, session] of course.sessions.entries()) {
        const sessionCode = `${course.code}-S${String(sessionIndex + 1).padStart(2, '0')}`;
        await transaction.request().input('code', sql.VarChar(30), course.code).input('session', sql.VarChar(50), sessionCode)
          .input('open', sql.Bit, sessionIndex % 3 !== 2).input('npk', sql.VarChar(50), assignedTeacher.npk)
          .query(`IF EXISTS (SELECT 1 FROM dbo.forum_session_settings WHERE course_code=@code AND session_code=@session)
              UPDATE dbo.forum_session_settings SET is_open=@open,updated_by=@npk,updated_at=SYSUTCDATETIME() WHERE course_code=@code AND session_code=@session;
            ELSE INSERT INTO dbo.forum_session_settings(course_code,session_code,is_open,updated_by) VALUES(@code,@session,@open,@npk);`);
      }
    }

    const submissionStatuses = ['pending_admin', 'pending_review', 'rejected', 'approved', 'published'];
    for (let index = 0; index < 200; index += 1) {
      const course = courses[index % courses.length];
      const courseIndex = index % courses.length;
      const session = course.sessions[index % course.sessions.length];
      const sessionId = sessionIdByCodeAndTitle.get(`${course.code}|${session.title}`);
      const title = `Data demo submission ${String(index + 1).padStart(3, '0')} - ${course.title}`;
      const status = index < 100 ? submissionStatuses[index % submissionStatuses.length] : 'pending_review';
      const submitter = staff[1 + (courseIndex % 6)];
      const reviewer = staff[7 + (index % 2)];
      const result = await transaction.request().input('sessionId', sql.BigInt, sessionId)
        .input('title', sql.NVarChar(200), title).input('submitter', sql.VarChar(50), submitter.npk)
        .input('reviewer', sql.VarChar(50), status === 'pending_admin' ? null : reviewer.npk)
        .input('status', sql.VarChar(24), status)
        .input('reviewedAt', sql.DateTime2, status === 'pending_admin' || status === 'pending_review' ? null : new Date(Date.now() - index * 3600000))
        .input('reviewerComment', sql.NVarChar(2000), status === 'rejected' ? 'Data demo: perlu perbaikan sebelum dipublikasikan.' : null)
        .input('publishedMaterialId', sql.BigInt, status === 'published' ? materialIdByCodeAndTitle.get(`${course.code}|${session.materials[0].title}`) : null)
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.material_submissions WHERE session_id=@sessionId AND title=@title AND submitted_by=@submitter)
            INSERT INTO dbo.material_submissions(session_id,title,material_type,content,sort_order,status,submitted_by,reviewer_npk,reviewer_comment,admin_comment,reviewed_at,published_material_id)
            VALUES(@sessionId,@title,'text',N'Data dummy untuk menguji alur moderasi materi.',90,@status,@submitter,@reviewer,@reviewerComment,
              CASE WHEN @status IN ('approved','published') THEN N'Data demo disetujui.' ELSE NULL END,@reviewedAt,@publishedMaterialId);
          SELECT TOP (1) submission_id FROM dbo.material_submissions WHERE session_id=@sessionId AND title=@title AND submitted_by=@submitter ORDER BY submission_id;`);
      const submissionId = result.recordset[0]?.submission_id;
      if (submissionId && status === 'pending_review') {
        const tokenHash = crypto.createHash('sha256').update(`demo-review:${submissionId}:${reviewer.npk}`).digest('hex');
        await transaction.request().input('submissionId', sql.BigInt, submissionId).input('reviewer', sql.VarChar(50), reviewer.npk)
          .input('tokenHash', sql.Char(64), tokenHash)
          .query(`IF NOT EXISTS (SELECT 1 FROM dbo.material_review_links WHERE submission_id=@submissionId AND reviewer_npk=@reviewer)
            INSERT INTO dbo.material_review_links(submission_id,reviewer_npk,token_hash,expires_at) VALUES(@submissionId,@reviewer,@tokenHash,DATEADD(day,7,SYSUTCDATETIME()));`);
      }
    }

    for (let index = 0; index < 100; index += 1) {
      const learner = demoLearners[index];
      const sender = staff[1 + (index % 6)];
      const title = `Pembaruan belajar demo ${String(index + 1).padStart(2, '0')}`;
      const category = ['course', 'assessment', 'forum', 'announcement', 'system'][index % 5];
      const result = await transaction.request().input('recipient', sql.VarChar(50), learner.npk)
        .input('title', sql.NVarChar(180), title).input('sender', sql.VarChar(50), sender.npk)
        .input('senderName', sql.NVarChar(150), sender.name).input('category', sql.VarChar(30), category)
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_notifications WHERE audience_role='student' AND recipient_npk=@recipient AND title=@title)
            INSERT INTO dbo.lms_notifications(audience_role,recipient_npk,title,message,category,priority,sender_npk,sender_role,sender_name,email_requested)
            VALUES('student',@recipient,@title,N'Ini notifikasi contoh untuk pengujian status baca dan halaman notifikasi.',
              @category,CASE WHEN @recipient LIKE '%0' THEN 'important' ELSE 'normal' END,@sender,'teacher',@senderName,0);
          SELECT TOP (1) notification_id FROM dbo.lms_notifications WHERE audience_role='student' AND recipient_npk=@recipient AND title=@title ORDER BY notification_id;`);
      const notificationId = result.recordset[0]?.notification_id;
      if (notificationId) {
        await transaction.request().input('id', sql.BigInt, notificationId).input('recipient', sql.VarChar(50), learner.npk)
          .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_notification_reads WHERE notification_id=@id AND recipient_role='student' AND recipient_npk=@recipient)
            INSERT INTO dbo.lms_notification_reads(notification_id,recipient_role,recipient_npk) VALUES(@id,'student',@recipient);`);
      }
    }

    for (let index = 0; index < demoLearners.length; index += 1) {
      const learner = demoLearners[index];
      const title = `Simulasi email akun demo ${String(index + 1).padStart(3, '0')}`;
      const notification = await transaction.request().input('recipient', sql.VarChar(50), learner.npk)
        .input('title', sql.NVarChar(180), title).input('sender', sql.VarChar(50), staff[0].npk)
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_notifications WHERE audience_role='student' AND recipient_npk=@recipient AND title=@title)
            INSERT INTO dbo.lms_notifications(audience_role,recipient_npk,title,message,category,priority,sender_npk,sender_role,sender_name,email_requested)
            VALUES('student',@recipient,@title,N'Simulasi status email akun demo. Email tidak dikirim ke alamat sungguhan.','system','normal',@sender,'admin',N'Dimas Demo Admin',1);
          SELECT TOP (1) notification_id FROM dbo.lms_notifications WHERE audience_role='student' AND recipient_npk=@recipient AND title=@title ORDER BY notification_id;`);
      const notificationId = notification.recordset[0]?.notification_id;
      if (notificationId) {
        const emailStatus = index % 10 === 0 ? 'retry' : (index % 5 === 0 ? 'sent' : 'failed');
        await transaction.request().input('notificationId', sql.BigInt, notificationId)
          .input('email', sql.NVarChar(254), learner.email).input('name', sql.NVarChar(150), learner.name)
          .input('status', sql.VarChar(20), emailStatus).input('attempts', sql.Int, emailStatus === 'sent' ? 1 : 2)
          .input('lastError', sql.NVarChar(500), emailStatus === 'sent' ? null : 'Data demo: status email hanya simulasi, pesan tidak dikirim.')
          .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_notification_email_outbox WHERE notification_id=@notificationId)
            INSERT INTO dbo.lms_notification_email_outbox(notification_id,recipient_email,recipient_name,status,attempts,next_attempt_at,last_error,sent_at)
            VALUES(@notificationId,@email,@name,@status,@attempts,
              CASE WHEN @status='retry' THEN DATEADD(day,30,SYSUTCDATETIME()) ELSE SYSUTCDATETIME() END,
              @lastError,CASE WHEN @status='sent' THEN SYSUTCDATETIME() ELSE NULL END);`);
      }
    }

    const notificationRows = await transaction.request().query(`SELECT TOP (1) notification_id FROM dbo.lms_notifications
      WHERE audience_role='student' AND recipient_npk='DLS-DEMO-STU-001' AND title='Simulasi email notifikasi demo' ORDER BY notification_id`);
    let emailDemoNotificationId = notificationRows.recordset[0]?.notification_id;
    if (!emailDemoNotificationId) {
      const created = await transaction.request().query(`INSERT INTO dbo.lms_notifications(audience_role,recipient_npk,title,message,category,priority,sender_npk,sender_role,sender_name,email_requested)
        OUTPUT INSERTED.notification_id VALUES('student','DLS-DEMO-STU-001',N'Simulasi email notifikasi demo',
        N'Email ini tidak dikirim. Data hanya contoh untuk menguji tampilan status pengiriman.',
        'system','normal','DLS-DEMO-ADM-001','admin',N'Dimas Demo Admin',1);`);
      emailDemoNotificationId = created.recordset[0]?.notification_id;
    }
    if (emailDemoNotificationId) {
      await transaction.request().input('id', sql.BigInt, emailDemoNotificationId)
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_notification_email_outbox WHERE notification_id=@id)
          INSERT INTO dbo.lms_notification_email_outbox(notification_id,recipient_email,recipient_name,status,attempts,last_error,sent_at)
          VALUES(@id,N'dina.demo@example.test',N'Dina Demo Operator','failed',2,N'Data demo: email contoh tidak dikirim.',NULL);`);
    }

    const demoAdmin = staff[0];
    const jobResult = await transaction.request().input('npk', sql.VarChar(50), demoAdmin.npk)
      .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_admin_jobs WHERE created_by=@npk AND job_type='demo_account_email' AND status='completed_with_errors' AND current_item IS NULL)
          INSERT INTO dbo.lms_admin_jobs(job_type,status,created_by,created_by_role,payload_ciphertext,payload_iv,payload_tag,total_items,processed_items,success_count,failure_count,finished_at)
          VALUES('demo_account_email','completed_with_errors',@npk,'admin',NULL,NULL,NULL,12,12,10,2,SYSUTCDATETIME());
        SELECT TOP (1) job_id FROM dbo.lms_admin_jobs WHERE created_by=@npk AND job_type='demo_account_email' AND status='completed_with_errors' AND current_item IS NULL ORDER BY job_id DESC;`);
    const demoJobId = jobResult.recordset[0]?.job_id;
    if (demoJobId) {
      for (let eventNo = 1; eventNo <= 12; eventNo += 1) {
        const itemStatus = eventNo % 6 === 0 ? 'failed' : 'completed';
        await transaction.request().input('jobId', sql.BigInt, demoJobId).input('itemNo', sql.Int, eventNo)
          .input('label', sql.NVarChar(300), `DLS-DEMO-STU-${String(eventNo).padStart(3, '0')}`)
          .input('itemStatus', sql.VarChar(20), itemStatus)
          .input('message', sql.NVarChar(500), itemStatus === 'failed' ? 'Data demo: alamat email simulasi tidak tersedia.' : 'Data demo: item selesai diproses.')
          .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_admin_job_events WHERE job_id=@jobId AND item_no=@itemNo)
            INSERT INTO dbo.lms_admin_job_events(job_id,item_no,item_label,item_status,message) VALUES(@jobId,@itemNo,@label,@itemStatus,@message);`);
      }
    }

    for (let index = 1; index <= 100; index += 1) {
      const learner = demoLearners[(index - 1) % demoLearners.length];
      const itemFailed = index % 10 === 0;
      const result = await transaction.request().input('npk', sql.VarChar(50), demoAdmin.npk)
        .input('currentItem', sql.NVarChar(300), learner.npk)
        .input('jobStatus', sql.VarChar(30), itemFailed ? 'completed_with_errors' : 'completed')
        .input('successCount', sql.Int, itemFailed ? 0 : 1).input('failureCount', sql.Int, itemFailed ? 1 : 0)
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_admin_jobs WHERE created_by=@npk AND job_type='demo_account_email' AND current_item=@currentItem)
            INSERT INTO dbo.lms_admin_jobs(job_type,status,created_by,created_by_role,payload_ciphertext,payload_iv,payload_tag,total_items,processed_items,success_count,failure_count,current_item,finished_at)
            VALUES('demo_account_email',@jobStatus,@npk,'admin',NULL,NULL,NULL,1,1,@successCount,@failureCount,@currentItem,SYSUTCDATETIME());
          SELECT TOP (1) job_id FROM dbo.lms_admin_jobs WHERE created_by=@npk AND job_type='demo_account_email' AND current_item=@currentItem ORDER BY job_id DESC;`);
      const jobId = result.recordset[0]?.job_id;
      if (jobId) {
        const itemNo = 1;
        await transaction.request().input('jobId', sql.BigInt, jobId).input('itemNo', sql.Int, itemNo)
          .input('label', sql.NVarChar(300), learner.npk).input('itemStatus', sql.VarChar(20), itemFailed ? 'failed' : 'completed')
          .input('message', sql.NVarChar(500), itemFailed ? 'Data demo: alamat email simulasi tidak tersedia.' : 'Data demo: item selesai diproses.')
          .query(`IF NOT EXISTS (SELECT 1 FROM dbo.lms_admin_job_events WHERE job_id=@jobId AND item_no=@itemNo)
            INSERT INTO dbo.lms_admin_job_events(job_id,item_no,item_label,item_status,message) VALUES(@jobId,@itemNo,@label,@itemStatus,@message);`);
      }
    }

    await transaction.commit();
    console.log(`Demo data seeded: ${staff.length} staff (1 admin, 100 teachers, 100 reviewers), ${courses.length} courses, ${demoLearners.length} students, ${learners.length} progress/grade snapshots, 100 forum threads, 400 student/teacher replies, 100 likes, 200 material submissions, 100 in-app notifications, 100 simulated email records, 100 queue-history jobs, and activity history.`);
    console.log('New demo staff accounts use SEED_DEMO_PASSWORD. Existing account passwords are unchanged.');
  } catch (error) {
    await transaction.rollback();
    throw error;
  } finally {
    await pool.close();
  }
}

main().catch(error => {
  console.error('Demo seed failed:', error.message);
  process.exitCode = 1;
});
