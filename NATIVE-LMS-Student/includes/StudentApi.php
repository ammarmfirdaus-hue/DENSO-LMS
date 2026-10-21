<?php
declare(strict_types=1);
require_once __DIR__ . '/Database.php';

function apiCourses(int $studentId): array
{
    $query = db()->prepare("SELECT c.course_id, c.course_code, c.title, c.description, c.category,
      c.thumbnail_path, COALESCE(e.progress, 0) AS progress, COALESCE(e.enrollment_status, 'available') AS enrollment_status
      FROM courses c LEFT JOIN course_enrollments e ON e.course_id=c.course_id AND e.student_id=:student_id
      WHERE c.status='active' ORDER BY c.updated_at DESC");
    $query->execute(['student_id' => $studentId]);
    return $query->fetchAll();
}

function apiDashboard(int $studentId): array
{
    $query = db()->prepare("SELECT COUNT(*) AS total_courses,
      SUM(CASE WHEN e.enrollment_status='active' THEN 1 ELSE 0 END) AS active_courses,
      SUM(CASE WHEN e.enrollment_status='completed' THEN 1 ELSE 0 END) AS completed_courses,
      COALESCE(AVG(CASE WHEN e.enrollment_status='completed' THEN e.progress END),0) AS average_progress
      FROM course_enrollments e WHERE e.student_id=:student_id");
    $query->execute(['student_id' => $studentId]);
    return $query->fetch() ?: ['total_courses' => 0, 'active_courses' => 0, 'completed_courses' => 0, 'average_progress' => 0];
}
