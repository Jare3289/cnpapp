<?php
require_once 'config.php';
session_start();

$user_id = 1; // Assuming admin user ID is 1

try {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link, icon, is_read) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    $data = [
        [$user_id, 'public_service', 'คำขอใหม่', 'มีนักเรียนส่งรายงานกิจกรรมสาธารณประโยชน์ใหม่ 1 รายการ', 'admin_public_service.html', 'bi bi-heart-fill', 0],
        [$user_id, 'system', 'อัปเดตระบบ', 'ระบบอัปเดตเป็นเวอร์ชันล่าสุดเรียบร้อยแล้ว', '#', 'bi bi-cpu', 0],
        [$user_id, 'attendance', 'รายงานเช็คชื่อ', 'สรุปยอดมาเรียนวันนี้: 98.5%', 'today_overview.html', 'bi bi-calendar-check', 1]
    ];

    foreach ($data as $row) {
        $stmt->execute($row);
    }

    echo "Seed notifications created!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
