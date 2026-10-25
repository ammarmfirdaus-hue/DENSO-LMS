const { getStudentManagementPool } = require('./student-auth');

function domainError(status, code, message) {
  const error = new Error(message);
  error.status = status;
  error.code = code;
  return error;
}

async function listActiveStudents(search = '', limit = 50) {
  const safeLimit = Math.max(1, Math.min(100, Number(limit) || 50));
  const term = String(search).trim().slice(0, 80);
  const query = term
    ? `SELECT npk,fullname FROM student_accounts
       WHERE account_type='student' AND status='active' AND (npk LIKE ? OR fullname LIKE ?)
       ORDER BY fullname LIMIT ${safeLimit}`
    : `SELECT npk,fullname FROM student_accounts
       WHERE account_type='student' AND status='active' ORDER BY fullname LIMIT ${safeLimit}`;
  const values = term ? [`%${term}%`, `%${term}%`] : [];
  const [rows] = await getStudentManagementPool().execute(query, values);
  return rows;
}

async function createTeacherRecommendation({ studentNpk, courseCode, teacherNpk, note }) {
  const connection = await getStudentManagementPool().getConnection();
  try {
    await connection.beginTransaction();
    const [students] = await connection.execute(
      "SELECT student_id FROM student_accounts WHERE npk=? AND account_type='student' AND status='active' FOR UPDATE",
      [studentNpk]
    );
    if (!students.length) throw domainError(404, 'STUDENT_NOT_FOUND', 'Student aktif tidak ditemukan.');
    const [courses] = await connection.execute(
      "SELECT course_id FROM courses WHERE course_code=? AND status='active' LIMIT 1 FOR UPDATE",
      [courseCode]
    );
    if (!courses.length) throw domainError(404, 'COURSE_NOT_FOUND', 'Course aktif tidak ditemukan di katalog Student. Sinkronkan katalog lalu coba lagi.');
    const [enrollments] = await connection.execute(
      "SELECT enrollment_status FROM course_enrollments WHERE student_id=? AND course_id=? LIMIT 1 FOR UPDATE",
      [students[0].student_id, courses[0].course_id]
    );
    if (enrollments[0] && ['active', 'completed'].includes(enrollments[0].enrollment_status)) {
      throw domainError(409, 'ALREADY_ENROLLED', 'Student sudah terdaftar pada course ini.');
    }
    const [pending] = await connection.execute(
      "SELECT request_id FROM course_enrollment_requests WHERE pending_key=CONCAT(?, ':', ?) LIMIT 1",
      [students[0].student_id, courses[0].course_id]
    );
    if (pending.length) throw domainError(409, 'REQUEST_PENDING', 'Permintaan untuk Student dan course ini masih menunggu keputusan Admin.');
    const [inserted] = await connection.execute(
      `INSERT INTO course_enrollment_requests(student_id,course_id,request_type,requested_by_npk,requested_by_role,request_note)
       VALUES(?,?,'teacher_recommendation',?,'teacher',?)`,
      [students[0].student_id, courses[0].course_id, teacherNpk, note || null]
    );
    await connection.commit();
    return Number(inserted.insertId);
  } catch (error) {
    await connection.rollback().catch(() => {});
    if (error.code === 'ER_DUP_ENTRY') throw domainError(409, 'REQUEST_PENDING', 'Permintaan untuk Student dan course ini masih menunggu keputusan Admin.');
    throw error;
  } finally { connection.release(); }
}

async function listEnrollmentRequests({ isAdmin, teacherNpk, status = 'all', limit = 500 }) {
  const safeLimit = Math.max(1, Math.min(500, Number(limit) || 500));
  const clauses = [];
  const params = [];
  if (!isAdmin) {
    clauses.push("r.request_type='teacher_recommendation' AND r.requested_by_npk=?");
    params.push(teacherNpk);
  }
  if (['pending', 'approved', 'rejected'].includes(status)) {
    clauses.push('r.status=?');
    params.push(status);
  }
  const where = clauses.length ? `WHERE ${clauses.join(' AND ')}` : '';
  const [rows] = await getStudentManagementPool().execute(
    `SELECT r.request_id,r.student_id,s.npk AS student_npk,s.fullname AS student_name,
        r.course_id,c.course_code,c.title AS course_title,r.request_type,r.requested_by_npk,
        r.requested_by_role,r.request_note,r.status,r.decision_by_npk,r.decision_note,
        r.requested_at,r.decided_at
      FROM course_enrollment_requests r
      JOIN student_accounts s ON s.student_id=r.student_id
      JOIN courses c ON c.course_id=r.course_id
      ${where}
      ORDER BY CASE r.status WHEN 'pending' THEN 0 ELSE 1 END,r.request_id DESC
      LIMIT ${safeLimit}`,
    params
  );
  return rows;
}

async function decideEnrollmentRequest({ requestId, action, adminNpk, note }) {
  const connection = await getStudentManagementPool().getConnection();
  try {
    await connection.beginTransaction();
    const [requests] = await connection.execute(
      'SELECT request_id,student_id,course_id,status FROM course_enrollment_requests WHERE request_id=? FOR UPDATE',
      [requestId]
    );
    const request = requests[0];
    if (!request) throw domainError(404, 'REQUEST_NOT_FOUND', 'Permintaan enrollment tidak ditemukan.');
    if (request.status !== 'pending') throw domainError(409, 'REQUEST_ALREADY_DECIDED', 'Permintaan ini sudah diputuskan Admin.');
    const [identity] = await connection.execute(
      `SELECT s.npk AS student_npk,s.fullname AS student_name,c.course_code,c.title AS course_title
       FROM student_accounts s JOIN courses c ON c.course_id=? WHERE s.student_id=? LIMIT 1`,
      [request.course_id, request.student_id]
    );
    if (!identity.length) throw domainError(409, 'REQUEST_DATA_MISSING', 'Data Student atau course pada permintaan tidak lagi tersedia.');
    Object.assign(request, identity[0]);

    if (action === 'approved') {
      const [students] = await connection.execute(
        "SELECT student_id FROM student_accounts WHERE student_id=? AND status='active' LIMIT 1 FOR UPDATE",
        [request.student_id]
      );
      if (!students.length) throw domainError(409, 'STUDENT_INACTIVE', 'Akun Student sudah tidak aktif. Aktifkan kembali sebelum menyetujui enrollment.');
      const [courses] = await connection.execute(
        "SELECT course_id FROM courses WHERE course_id=? AND status='active' LIMIT 1 FOR UPDATE",
        [request.course_id]
      );
      if (!courses.length) throw domainError(409, 'COURSE_INACTIVE', 'Course sudah tidak aktif sehingga tidak dapat disetujui.');
      const [enrollments] = await connection.execute(
        'SELECT enrollment_status FROM course_enrollments WHERE student_id=? AND course_id=? LIMIT 1 FOR UPDATE',
        [request.student_id, request.course_id]
      );
      if (enrollments[0] && ['active', 'completed'].includes(enrollments[0].enrollment_status)) {
        throw domainError(409, 'ALREADY_ENROLLED', 'Student sudah memiliki akses course ini.');
      }
      if (enrollments[0]?.enrollment_status === 'dropped') {
        await connection.execute(
          "UPDATE course_enrollments SET enrollment_status='active',completed_at=NULL,updated_at=CURRENT_TIMESTAMP WHERE student_id=? AND course_id=?",
          [request.student_id, request.course_id]
        );
      } else {
        await connection.execute(
          "INSERT INTO course_enrollments(student_id,course_id,progress,enrollment_status) VALUES(?,?,0,'active')",
          [request.student_id, request.course_id]
        );
      }
    }
    await connection.execute(
      `UPDATE course_enrollment_requests SET status=?,decision_by_npk=?,decision_note=?,decided_at=UTC_TIMESTAMP(),updated_at=CURRENT_TIMESTAMP
       WHERE request_id=?`,
      [action, adminNpk, note || null, requestId]
    );
    await connection.commit();
    return { studentNpk: request.student_npk, studentName: request.student_name, courseCode: request.course_code, courseTitle: request.course_title };
  } catch (error) {
    await connection.rollback().catch(() => {});
    throw error;
  } finally { connection.release(); }
}

module.exports = { listActiveStudents, createTeacherRecommendation, listEnrollmentRequests, decideEnrollmentRequest };
