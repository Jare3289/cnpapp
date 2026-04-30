<?php
// api/admin/executive_overview.php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d');

try {
    // 1. Overall Completion KPI
    // We count rooms that have students
    $totalRoomsStmt = $pdo->query("SELECT COUNT(DISTINCT room) FROM students WHERE room IS NOT NULL AND room != ''");
    $totalRooms = (int)$totalRoomsStmt->fetchColumn();
    
    // We count rooms that have at least one record in attendance for today (daily type)
    $checkedRoomsStmt = $pdo->prepare("SELECT COUNT(DISTINCT class_name) FROM attendance WHERE date = ? AND type = 'daily'");
    $checkedRoomsStmt->execute([$date]);
    $checkedCount = (int)$checkedRoomsStmt->fetchColumn();

    // 2. Student Distribution
    $stmt = $pdo->prepare("SELECT 
        SUM(CASE WHEN status = 'มา' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'ขาด' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = 'สาย' THEN 1 ELSE 0 END) as late,
        SUM(CASE WHEN status IN ('ลา', 'ป่วย') THEN 1 ELSE 0 END) as leave_count
        FROM attendance WHERE date = ? AND type = 'daily'");
    $stmt->execute([$date]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC);

    // Ensure we have an array even if no data exists for today
    if (!$summary) {
        $summary = ['present' => 0, 'absent' => 0, 'late' => 0, 'leave_count' => 0];
    }

    // 3. Grade-Level Performance (Using actual grade_level from students)
    $gradeStmt = $pdo->prepare("
        SELECT 
            s.grade_level as grade,
            SUM(CASE WHEN a.status = 'มา' THEN 1 ELSE 0 END) * 100.0 / COUNT(a.id) as attendance_rate
        FROM attendance a
        JOIN students s ON a.student_id = s.id
        WHERE a.date = ? AND a.type = 'daily'
        GROUP BY s.grade_level
        ORDER BY s.grade_level ASC
    ");
    $gradeStmt->execute([$date]);
    $gradeStats = $gradeStmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Critical Rooms (Top 5 Highest Absence)
    $badRoomsStmt = $pdo->prepare("
        SELECT class_name as room, COUNT(*) as absent_count
        FROM attendance
        WHERE date = ? AND status = 'ขาด' AND type = 'daily'
        GROUP BY class_name
        ORDER BY absent_count DESC
        LIMIT 5
    ");
    $badRoomsStmt->execute([$date]);
    $criticalRooms = $badRoomsStmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Room Gender Summary
    // Note: We use LEFT JOIN to show rooms that haven't been checked yet too
    $roomGenderStmt = $pdo->prepare("
        SELECT 
            s.room,
            SUM(CASE WHEN s.gender = 'ชาย' THEN 1 ELSE 0 END) as total_m,
            SUM(CASE WHEN s.gender = 'หญิง' THEN 1 ELSE 0 END) as total_f,
            SUM(CASE WHEN a.status = 'มา' AND s.gender = 'ชาย' THEN 1 ELSE 0 END) as present_m,
            SUM(CASE WHEN a.status = 'มา' AND s.gender = 'หญิง' THEN 1 ELSE 0 END) as present_f,
            SUM(CASE WHEN (a.status = 'ขาด' OR a.status = 'สาย' OR a.status = 'ลา' OR a.status = 'ป่วย') AND s.gender = 'ชาย' THEN 1 ELSE 0 END) as absent_m,
            SUM(CASE WHEN (a.status = 'ขาด' OR a.status = 'สาย' OR a.status = 'ลา' OR a.status = 'ป่วย') AND s.gender = 'หญิง' THEN 1 ELSE 0 END) as absent_f,
            COUNT(a.id) as checked_count
        FROM students s
        LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ? AND a.type = 'daily'
        WHERE s.room IS NOT NULL AND s.room != ''
        GROUP BY s.room
        ORDER BY s.room ASC
    ");
    $roomGenderStmt->execute([$date]);
    $roomGenderStats = $roomGenderStmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Check-in Timeline (Hourly)
    $timelineStmt = $pdo->prepare("
        SELECT HOUR(created_at) as hour, COUNT(*) as count
        FROM attendance
        WHERE date = ? AND type = 'daily'
        GROUP BY hour
        ORDER BY hour ASC
    ");
    $timelineStmt->execute([$date]);
    $timeline = $timelineStmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. At-Risk Students (Missing today + high historical absence)
    $atRiskStmt = $pdo->prepare("
        SELECT s.first_name_th, s.last_name_th, s.room, 
               (SELECT COUNT(*) FROM attendance a2 WHERE a2.student_id = s.id AND a2.status = 'ขาด') as total_absent
        FROM students s
        JOIN attendance a ON s.id = a.student_id
        WHERE a.date = ? AND a.status = 'ขาด' AND a.type = 'daily'
        ORDER BY total_absent DESC
        LIMIT 10
    ");
    $atRiskStmt->execute([$date]);
    $atRisk = $atRiskStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'kpis' => [
            'total_rooms' => $totalRooms,
            'checked_rooms' => $checkedCount,
            'completion_rate' => $totalRooms > 0 ? round(($checkedCount / $totalRooms) * 100, 1) : 0,
            'attendance_summary' => [
                'present' => (int)$summary['present'],
                'absent' => (int)$summary['absent'],
                'late' => (int)$summary['late'],
                'leave' => (int)$summary['leave_count'],
                'total_checked' => (int)$summary['present'] + (int)$summary['absent'] + (int)$summary['late'] + (int)$summary['leave_count']
            ]
        ],
        'grade_stats' => $gradeStats,
        'critical_rooms' => $criticalRooms,
        'room_gender_stats' => $roomGenderStats,
        'timeline' => $timeline,
        'at_risk' => $atRisk
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
