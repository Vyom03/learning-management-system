-- Create video content tables for LMS
-- Run this in MySQL to create video content and watch progress tables

USE loginauth;

-- Create video_content table (if not exists)
CREATE TABLE IF NOT EXISTS video_content (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    youtube_url VARCHAR(500) NOT NULL,
    youtube_id VARCHAR(50) NOT NULL,
    min_watch_time_minutes INT NOT NULL DEFAULT 2,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_course_id (course_id),
    INDEX idx_youtube_id (youtube_id),
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create video_watch_progress table (if not exists)
CREATE TABLE IF NOT EXISTS video_watch_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    video_content_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    watch_time_seconds INT NOT NULL DEFAULT 0,
    is_completed TINYINT(1) NOT NULL DEFAULT 0,
    last_watched_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_video_content_id (video_content_id),
    INDEX idx_student_id (student_id),
    INDEX idx_is_completed (is_completed),
    UNIQUE KEY unique_video_student (video_content_id, student_id),
    FOREIGN KEY (video_content_id) REFERENCES video_content(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Video content tables created successfully!' as result;

