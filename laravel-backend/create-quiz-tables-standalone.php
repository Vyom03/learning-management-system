<?php
// Standalone script to create quiz tables - no Laravel dependencies
// Just needs PHP and MySQL PDO extension

$host = 'localhost';
$dbname = 'loginauth';
$username = 'root';
$password = 'temp@12345';

try {
    echo "Connecting to database...\n";
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected!\n\n";
    
    echo "Creating quizzes table...\n";
    $sql1 = "CREATE TABLE IF NOT EXISTS quizzes (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        course_id BIGINT UNSIGNED NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT NULL,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        INDEX idx_course_id (course_id),
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql1);
    echo "✓ quizzes table created\n\n";
    
    echo "Creating quiz_questions table...\n";
    $sql2 = "CREATE TABLE IF NOT EXISTS quiz_questions (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        quiz_id BIGINT UNSIGNED NOT NULL,
        question TEXT NOT NULL,
        options JSON NOT NULL,
        correct_answer INT NOT NULL,
        points INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL,
        INDEX idx_quiz_id (quiz_id),
        FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $pdo->exec($sql2);
    echo "✓ quiz_questions table created\n\n";
    
    echo "SUCCESS! Quiz tables created.\n";
    
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

