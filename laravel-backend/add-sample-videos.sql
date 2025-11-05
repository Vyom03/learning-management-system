-- Add sample YouTube learning videos for testing
-- Run this after creating the video_content table
-- Note: Replace course_id with actual course IDs from your database

USE loginauth;

-- Sample educational YouTube videos for testing
-- These are popular educational channels with good learning content

-- Example 1: JavaScript Tutorial (FreeCodeCamp)
-- Video: JavaScript Full Course for Beginners
INSERT INTO video_content (course_id, title, description, youtube_url, youtube_id, min_watch_time_minutes, created_at, updated_at)
SELECT 
    1 as course_id, -- Replace with actual course_id
    'JavaScript Full Course for Beginners',
    'Complete JavaScript tutorial covering all the fundamentals of JavaScript programming. Learn variables, functions, arrays, objects, and more.',
    'https://www.youtube.com/watch?v=PkZNo7MFNFg',
    'PkZNo7MFNFg',
    2,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM video_content WHERE youtube_id = 'PkZNo7MFNFg');

-- Example 2: Python Tutorial (freeCodeCamp)
-- Video: Python for Beginners
INSERT INTO video_content (course_id, title, description, youtube_url, youtube_id, min_watch_time_minutes, created_at, updated_at)
SELECT 
    1 as course_id, -- Replace with actual course_id
    'Python for Beginners - Full Course',
    'Learn Python programming from scratch. This course covers Python basics, data structures, functions, and object-oriented programming.',
    'https://www.youtube.com/watch?v=eIrMbAQSU34',
    'eIrMbAQSU34',
    2,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM video_content WHERE youtube_id = 'eIrMbAQSU34');

-- Example 3: React Tutorial (freeCodeCamp)
-- Video: React Full Course
INSERT INTO video_content (course_id, title, description, youtube_url, youtube_id, min_watch_time_minutes, created_at, updated_at)
SELECT 
    1 as course_id, -- Replace with actual course_id
    'React Full Course for Beginners',
    'Complete React.js tutorial covering components, props, state, hooks, and building real-world applications.',
    'https://www.youtube.com/watch?v=bMknfKXIFA8',
    'bMknfKXIFA8',
    2,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM video_content WHERE youtube_id = 'bMknfKXIFA8');

-- Example 4: HTML & CSS Tutorial (freeCodeCamp)
-- Video: HTML & CSS Full Course
INSERT INTO video_content (course_id, title, description, youtube_url, youtube_id, min_watch_time_minutes, created_at, updated_at)
SELECT 
    1 as course_id, -- Replace with actual course_id
    'HTML & CSS Full Course - Beginner to Pro',
    'Complete HTML and CSS course for web development beginners. Learn to build beautiful and responsive websites.',
    'https://www.youtube.com/watch?v=G3e-cpL7ofc',
    'G3e-cpL7ofc',
    2,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM video_content WHERE youtube_id = 'G3e-cpL7ofc');

-- Example 5: Data Structures & Algorithms (freeCodeCamp)
-- Video: Data Structures and Algorithms
INSERT INTO video_content (course_id, title, description, youtube_url, youtube_id, min_watch_time_minutes, created_at, updated_at)
SELECT 
    1 as course_id, -- Replace with actual course_id
    'Data Structures and Algorithms for Beginners',
    'Learn fundamental data structures and algorithms. Topics include arrays, linked lists, stacks, queues, trees, and sorting algorithms.',
    'https://www.youtube.com/watch?v=RBSGKlAvoiM',
    'RBSGKlAvoiM',
    2,
    NOW(),
    NOW()
WHERE NOT EXISTS (SELECT 1 FROM video_content WHERE youtube_id = 'RBSGKlAvoiM');

SELECT 'Sample videos added successfully! Make sure to update course_id values with actual course IDs from your database.' as result;

