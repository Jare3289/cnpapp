<?php
require_once '../config.php';

try {
    // 1. Add recorded_by to attendance
    try {
        $pdo->exec("ALTER TABLE attendance ADD COLUMN recorded_by INT DEFAULT NULL AFTER remark");
        echo "Added recorded_by to attendance\n";
    } catch (Exception $e) { echo "recorded_by already exists or error: " . $e->getMessage() . "\n"; }

    // 2. Add is_active and other metadata to students
    try {
        $pdo->exec("ALTER TABLE students ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER last_name_th");
        echo "Added is_active to students\n";
    } catch (Exception $e) { echo "is_active already exists or error: " . $e->getMessage() . "\n"; }
    
    try {
        $pdo->exec("ALTER TABLE students ADD COLUMN student_id_card VARCHAR(20) DEFAULT NULL AFTER student_id");
        echo "Added student_id_card to students\n";
    } catch (Exception $e) { echo "student_id_card already exists or error: " . $e->getMessage() . "\n"; }

    // 3. Create point system tables (Credit / At-risk tracking)
    $pdo->exec("CREATE TABLE IF NOT EXISTS point_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(100) NOT NULL,
        is_positive TINYINT(1) DEFAULT 1
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS point_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        category_id INT,
        item_name VARCHAR(255) NOT NULL,
        points INT DEFAULT 0,
        FOREIGN KEY (category_id) REFERENCES point_categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    $pdo->exec("CREATE TABLE IF NOT EXISTS point_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        item_id INT,
        points INT,
        remark TEXT,
        recorded_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (item_id) REFERENCES point_items(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Point system tables created/verified.\n";
    
    // 4. Create evaluations table (Academic risk)
    $pdo->exec("CREATE TABLE IF NOT EXISTS student_evaluations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        eval_type VARCHAR(50) COMMENT 'academic, credit, health',
        score DECIMAL(5,2),
        risk_level VARCHAR(20) COMMENT 'low, medium, high',
        teacher_comment TEXT,
        recorded_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    echo "Evaluations table created/verified.\n";

} catch (PDOException $e) {
    echo "CRITICAL ERROR: " . $e->getMessage();
}
?>
