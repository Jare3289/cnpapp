<?php
// api/teacher/room-report.php
require_once '../../config.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');

try {
    // 1. Get all classes
    $stmt = $pdo->query("SELECT DISTINCT class_name FROM students WHERE class_name IS NOT NULL ORDER BY class_name ASC");
    $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Get attendance summary for each class for today
    $report = [];
    foreach ($classes as $className) {
        $stmt = $pdo->prepare("SELECT 
            COUNT(s.id) as total,
            SUM(CASE WHEN a.status = 'มา' THEN 1 ELSE 0 END) as present,
            SUM(CASE WHEN a.status = 'ขาด' THEN 1 ELSE 0 END) as absent,
            SUM(CASE WHEN a.status IN ('ลา', 'ป่วย') THEN 1 ELSE 0 END) as leave_count,
            SUM(CASE WHEN a.status = 'สาย' THEN 1 ELSE 0 END) as late,
            MAX(a.recorded_at) as last_update
            FROM students s
            LEFT JOIN attendance a ON s.id = a.student_id AND a.attendance_date = ? AND a.type = 'daily'
            WHERE s.class_name = ?
            GROUP BY s.class_name");
        $stmt->execute([$date, $className]);
        $row = $stmt->fetch();

        if ($row) {
            $report[] = [
                'class_name' => $className,
                'total' => (int)$row['total'],
                'present' => (int)$row['present'],
                'absent' => (int)$row['absent'],
                'leave' => (int)$row['leave_count'],
                'late' => (int)$row['late'],
                'status' => ($row['present'] + $row['absent'] + $row['leave_count'] + $row['late'] > 0) ? 'Checked' : 'Pending',
                'last_update' => $row['last_update']
            ];
        } else {
            $report[] = [
                'class_name' => $className,
                'total' => 0,
                'present' => 0,
                'absent' => 0,
                'leave' => 0,
                'late' => 0,
                'status' => 'No Data'
            ];
        }
    }

    echo json_encode(['success' => true, 'report' => $report]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
