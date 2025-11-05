USE loginauth;

-- Critical indexes for performance
-- Note: These will fail if indexes already exist, but that's okay

CREATE INDEX idx_enrollments_student_id ON enrollments(student_id);
CREATE INDEX idx_enrollments_course_id ON enrollments(course_id);
CREATE INDEX idx_enrollments_status ON enrollments(status);
CREATE INDEX idx_courses_teacher_id ON courses(teacher_id);

SELECT 'Indexes created successfully!' as result;

