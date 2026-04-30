<?php
require_once 'config.php';

try {
    // 1. สร้างตาราง roles (ไม่อยู่ใน transaction เพราะสร้างตารางมักทำ implicit commit)
    $pdo->exec("CREATE TABLE IF NOT EXISTS roles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL UNIQUE,
        label VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // 2. เพิ่มข้อมูลพื้นฐาน
    $pdo->exec("INSERT IGNORE INTO roles (name, label) VALUES 
        ('admin', 'ผู้ดูแลระบบ'),
        ('teacher', 'ครู'),
        ('student', 'นักเรียน')");

    // 3. ตรวจสอบและเพิ่มคอลัมน์ role_id
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('role_id', $columns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN role_id INT NULL AFTER role");
    }

    // 4. อัปเดต role_id ตามค่า role เดิม
    $pdo->exec("UPDATE users u SET role_id = (SELECT id FROM roles r WHERE r.name = u.role) WHERE role_id IS NULL");

    echo json_encode(['success' => true, 'message' => 'สร้างตาราง Roles และเชื่อมต่อข้อมูลเรียบร้อยแล้ว']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
