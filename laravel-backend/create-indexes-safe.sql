USE loginauth;

-- Safe index creation (checks if exists first)
-- Enrollments table indexes
SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = 'loginauth' AND table_name = 'enrollments' AND index_name = 'idx_enrollments_student_id');
SET @sqlstmt := IF(@exist = 0, 'CREATE INDEX idx_enrollments_student_id ON enrollments(student_id)', 'SELECT "Index idx_enrollments_student_id already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = 'loginauth' AND table_name = 'enrollments' AND index_name = 'idx_enrollments_course_id');
SET @sqlstmt := IF(@exist = 0, 'CREATE INDEX idx_enrollments_course_id ON enrollments(course_id)', 'SELECT "Index idx_enrollments_course_id already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = 'loginauth' AND table_name = 'enrollments' AND index_name = 'idx_enrollments_status');
SET @sqlstmt := IF(@exist = 0, 'CREATE INDEX idx_enrollments_status ON enrollments(status)', 'SELECT "Index idx_enrollments_status already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @exist := (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = 'loginauth' AND table_name = 'courses' AND index_name = 'idx_courses_teacher_id');
SET @sqlstmt := IF(@exist = 0, 'CREATE INDEX idx_courses_teacher_id ON courses(teacher_id)', 'SELECT "Index idx_courses_teacher_id already exists"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Indexes check/creation completed!' as result;

