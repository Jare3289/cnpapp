<?php
// api/teacher/overview.php
require_once '../../config.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401); exit;
}

$date = $_GET['date'] ?? date('Y-m-d');

try {
    // 1. Attendance Summary (Rooms)
    $stmt = $pdo->query("SELECT DISTINCT class_name FROM students WHERE class_name IS NOT NULL");
    $allClasses = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $totalClasses = count($allClasses);

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT class_name) FROM attendance WHERE attendance_date = ? AND type = 'daily'");
    $stmt->execute([$date]);
    $checkedClasses = $stmt->fetchColumn();

    // 2. Student totals today
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN status = 'มา' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'ขาด' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status IN ('ลา', 'ป่วย') THEN 1 ELSE 0 END) as leave_count,
        SUM(CASE WHEN status = 'สาย' THEN 1 ELSE 0 END) as late
        FROM attendance WHERE attendance_date = ? AND type = 'daily'");
    $stmt->execute([$date]);
    $summary = $stmt->fetch();

    // 3. At-risk students (Absence > 5 days total in this month)
    $stmt = $pdo->prepare("SELECT s.id, s.student_id, s.prefix, s.first_name_th, s.last_name_th, s.class_name, COUNT(a.id) as absent_count
        FROM students s
        JOIN attendance a ON s.id = a.student_id
        WHERE a.status = 'ขาด' AND a.attendance_date LIKE ?
        GROUP BY s.id
        HAVING absent_count >= 5
        ORDER BY absent_count DESC LIMIT 10");
    $stmt->execute([date('Y-m') . '%']);
    $atRisk = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'summary' => [
            'total_rooms' => $totalClasses,
            'checked_rooms' => $checkedClasses,
            'pending_rooms' => $totalClasses - $checkedClasses,
            'present' => (int)$summary['present'],
            'absent' => (int)$summary['absent'],
            'leave' => (int)$summary['leave_count'],
            'late' => (int)$summary['late'],
        ],
        'atRisk' => $atRisk
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
