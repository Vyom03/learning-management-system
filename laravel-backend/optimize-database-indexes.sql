-- Optimize database with indexes for better query performance

USE loginauth;

-- Indexes for enrollments table (if not exists)
ALTER TABLE enrollments ADD INDEX IF NOT EXISTS idx_student_id (student_id);
ALTER TABLE enrollments ADD INDEX IF NOT EXISTS idx_course_id (course_id);
ALTER TABLE enrollments ADD INDEX IF NOT EXISTS idx_status (status);
ALTER TABLE enrollments ADD INDEX IF NOT EXISTS idx_student_course (student_id, course_id);

-- Indexes for courses table
ALTER TABLE courses ADD INDEX IF NOT EXISTS idx_teacher_id (teacher_id);
ALTER TABLE courses ADD INDEX IF NOT EXISTS idx_created_at (created_at);

-- Indexes for users table (if model_has_roles is used)
-- These are likely already indexed by Spatie Permission package

-- Composite indexes for common queries
ALTER TABLE enrollments ADD INDEX IF NOT EXISTS idx_student_status (student_id, status);
ALTER TABLE enrollments ADD INDEX IF NOT EXISTS idx_course_status (course_id, status);

-- Indexes for forum tables (if they exist)
-- Will fail gracefully if tables don't exist
SET @exist := (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'loginauth' AND table_name = 'forum_topics');
SET @sqlstmt := IF(@exist > 0, 'ALTER TABLE forum_topics ADD INDEX IF NOT EXISTS idx_course_created (course_id, created_at)', 'SELECT "forum_topics table does not exist"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Indexes for quizzes table (if they exist)
SET @exist := (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'loginauth' AND table_name = 'quizzes');
SET @sqlstmt := IF(@exist > 0, 'ALTER TABLE quizzes ADD INDEX IF NOT EXISTS idx_course_created (course_id, created_at)', 'SELECT "quizzes table does not exist"');
PREPARE stmt FROM @sqlstmt;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Database indexes optimized!' as status;

