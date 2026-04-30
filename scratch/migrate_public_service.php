<?php
require_once 'config.php';

try {
    // 1. Create public_service_records table
    $pdo->exec("CREATE TABLE IF NOT EXISTS public_service_records (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(20) NOT NULL,
        activity_name VARCHAR(255) NOT NULL,
        location VARCHAR(255),
        activity_date DATE NOT NULL,
        duration DECIMAL(5,2) DEFAULT 0, -- Store as decimal (e.g., 3.05 hours)
        duration_unit VARCHAR(20) DEFAULT 'ครั้ง', -- Default unit (occurrences) as per previous context
        certifier_name VARCHAR(255), -- Name of the person who certifies the activity
        approver_id INT, -- User ID of the admin/teacher who approved
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        academic_year VARCHAR(4),
        semester VARCHAR(1),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (student_id),
        INDEX (status),
        INDEX (academic_year, semester)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    echo json_encode(['success' => true, 'message' => 'สร้างตาราง กิจกรรมสาธารณประโยชน์ เรียบร้อยแล้ว']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
