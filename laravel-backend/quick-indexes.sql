-- Quick indexes for performance - run this in MySQL

USE loginauth;

-- Enrollments table indexes (will fail if exists, but that's okay)
CREATE INDEX idx_enrollments_student_id ON enrollments(student_id);
CREATE INDEX idx_enrollments_course_id ON enrollments(course_id);
CREATE INDEX idx_enrollments_status ON enrollments(status);
CREATE INDEX idx_enrollments_student_course ON enrollments(student_id, course_id);

-- Courses table indexes
CREATE INDEX idx_courses_teacher_id ON courses(teacher_id);
CREATE INDEX idx_courses_created_at ON courses(created_at);

