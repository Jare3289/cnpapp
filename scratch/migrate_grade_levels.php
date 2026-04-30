<?php
require_once 'config.php';

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS grade_levels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        grade_name VARCHAR(100) NOT NULL UNIQUE,
        head_teacher_id INT NULL,
        room_count INT DEFAULT 0,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Seed initial grade levels if empty
    $check = $pdo->query("SELECT COUNT(*) FROM grade_levels");
    if ($check->fetchColumn() == 0) {
        $grades = [
            ['มัธยมศึกษาปีที่ 1', 14],
            ['มัธยมศึกษาปีที่ 2', 14],
            ['มัธยมศึกษาปีที่ 3', 13],
            ['มัธยมศึกษาปีที่ 4', 12],
            ['มัธยมศึกษาปีที่ 5', 12],
            ['มัธยมศึกษาปีที่ 6', 11]
        ];
        $stmt = $pdo->prepare("INSERT INTO grade_levels (grade_name, room_count) VALUES (?, ?)");
        foreach ($grades as $g) {
            $stmt->execute($g);
        }
    }

    echo json_encode(['success' => true, 'message' => 'สร้างตารางระดับชั้นเรียนเรียบร้อยแล้ว']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
