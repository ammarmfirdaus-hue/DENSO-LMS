require('dotenv').config();

const bcrypt = require('bcryptjs');
const { sql, pool } = require('../src/config/database');

const demoPassword = process.env.SEED_DEMO_PASSWORD || '';
if (process.env.SEED_DEMO !== 'true') {
  console.error('Refusing to seed: set SEED_DEMO=true for this local demo-data command.');
  process.exit(1);
}
if (demoPassword.length < 12 || Buffer.byteLength(demoPassword, 'utf8') > 72) {
  console.error('Set SEED_DEMO_PASSWORD to a demo-only password with 12-72 UTF-8 bytes.');
  process.exit(1);
}

const staff = [
  { npk: 'DLS-DEMO-ADM-001', name: 'Dimas Demo Admin', email: 'admin.demo@example.test', role: 'admin' },
  { npk: 'DLS-DEMO-TCH-001', name: 'Tara Demo Teacher', email: 'teacher.demo@example.test', role: 'teacher' }
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

const learners = [
  { npk: 'DLS-DEMO-STU-001', code: courses[0].code, progress: 100, status: 'completed' },
  { npk: 'DLS-DEMO-STU-002', code: courses[1].code, progress: 55, status: 'active' },
  { npk: 'DLS-DEMO-STU-003', code: courses[2].code, progress: 20, status: 'active' }
];

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
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.employee_accounts WHERE employee_npk=@npk)
          INSERT INTO dbo.employee_accounts(employee_npk,fullname,email,password_hash,role)
          VALUES(@npk,@name,@email,@hash,@role);`);
    }

    for (const course of courses) {
      await transaction.request()
        .input('code', sql.VarChar(30), course.code)
        .input('title', sql.NVarChar(200), course.title)
        .input('description', sql.NVarChar(4000), course.description)
        .input('category', sql.VarChar(20), course.category)
        .input('teacher', sql.VarChar(50), staff[1].npk)
        .query(`IF EXISTS (SELECT 1 FROM dbo.courses WHERE course_code=@code)
            UPDATE dbo.courses SET title=@title,description=@description,category=@category,status='active',updated_by=@teacher,updated_at=SYSUTCDATETIME()
            WHERE course_code=@code;
          ELSE
            INSERT INTO dbo.courses(course_code,title,description,category,status,created_by,updated_by)
            VALUES(@code,@title,@description,@category,'active',@teacher,@teacher);`);

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

        for (let materialIndex = 0; materialIndex < session.materials.length; materialIndex += 1) {
          const material = session.materials[materialIndex];
          await transaction.request()
            .input('sessionId', sql.BigInt, sessionId)
            .input('title', sql.NVarChar(200), material.title)
            .input('type', sql.VarChar(20), material.type)
            .input('content', sql.NVarChar(sql.MAX), material.content)
            .input('sortOrder', sql.Int, materialIndex + 1)
            .query(`IF NOT EXISTS (SELECT 1 FROM dbo.course_materials WHERE session_id=@sessionId AND title=@title)
              INSERT INTO dbo.course_materials(session_id,title,material_type,content,sort_order)
              VALUES(@sessionId,@title,@type,@content,@sortOrder);`);
        }
      }
    }

    const activities = [
      ['course_created', 'Data demo: membuat course keselamatan kerja.'],
      ['session_created', 'Data demo: menambahkan sesi pengenalan K3 dan APD.'],
      ['material_created', 'Data demo: menambahkan panduan APD area produksi.']
    ];
    for (const [type, description] of activities) {
      await transaction.request().input('npk', sql.VarChar(50), staff[1].npk)
        .input('type', sql.VarChar(50), type).input('description', sql.NVarChar(500), description)
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.activity_logs WHERE employee_npk=@npk AND activity_type=@type AND description=@description)
          INSERT INTO dbo.activity_logs(employee_npk,activity_type,description) VALUES(@npk,@type,@description);`);
    }

    for (const learner of learners) {
      const updatedAt = new Date(Date.now() - (learners.indexOf(learner) + 1) * 86400000);
      await transaction.request().input('npk', sql.VarChar(50), learner.npk)
        .input('code', sql.VarChar(30), learner.code).input('progress', sql.TinyInt, learner.progress)
        .input('status', sql.VarChar(20), learner.status).input('updated', sql.DateTime2, updatedAt)
        .query(`IF EXISTS (SELECT 1 FROM dbo.student_progress_snapshots WHERE student_npk=@npk AND course_code=@code)
            UPDATE dbo.student_progress_snapshots SET progress=@progress,enrollment_status=@status,source_updated_at=@updated,synced_at=SYSUTCDATETIME()
            WHERE student_npk=@npk AND course_code=@code;
          ELSE
            INSERT INTO dbo.student_progress_snapshots(student_npk,course_code,progress,enrollment_status,source_updated_at)
            VALUES(@npk,@code,@progress,@status,@updated);`);
    }

    const forumThreads = [
      { npk: learners[0].npk, code: courses[0].code, title: 'Diskusi demo: APD untuk area produksi', body: 'Data dummy forum. Bagikan pertanyaan tentang pemilihan APD yang sesuai.' },
      { npk: learners[1].npk, code: courses[1].code, title: 'Diskusi demo: audit 5S', body: 'Data dummy forum. Apa temuan pertama yang perlu ditindaklanjuti saat audit 5S?' },
      { npk: learners[2].npk, code: courses[2].code, title: 'Diskusi demo: ide Kaizen sederhana', body: 'Data dummy forum. Tuliskan satu ide perbaikan kecil untuk proses kerja.' }
    ];
    for (const thread of forumThreads) {
      await transaction.request().input('npk', sql.VarChar(50), thread.npk)
        .input('code', sql.VarChar(30), thread.code).input('title', sql.NVarChar(200), thread.title)
        .input('body', sql.NVarChar(sql.MAX), thread.body)
        .query(`IF NOT EXISTS (SELECT 1 FROM dbo.forum_threads WHERE author_npk=@npk AND course_code=@code AND title=@title)
          INSERT INTO dbo.forum_threads(author_npk,course_code,title,body,status) VALUES(@npk,@code,@title,@body,'visible');`);
    }

    await transaction.commit();
    console.log('Demo data seeded for 2 staff accounts, 3 courses, learning content, activity, progress snapshots, and forum threads.');
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
