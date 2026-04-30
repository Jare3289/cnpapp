<?php
require_once 'config.php';

try {
    // 1. Create notifications table
    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL, -- Recipient
        type VARCHAR(50), -- 'public_service', 'attendance', 'system'
        title VARCHAR(255) NOT NULL,
        message TEXT,
        link VARCHAR(255),
        icon VARCHAR(50) DEFAULT 'bi bi-bell',
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id),
        INDEX (is_read),
        INDEX (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    echo json_encode(['success' => true, 'message' => 'สร้างตารางระบบแจ้งเตือนเรียบร้อยแล้ว']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
