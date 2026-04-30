<?php
// api/admin/dashboard_stats.php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $today = date('Y-m-d');
    
    // 1. Basic Stats
    $totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $totalTeachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $totalClasses = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
    $totalSubjects = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
    $studentTeacherRatio = $totalTeachers > 0 ? round($totalStudents / $totalTeachers, 1) : 0;

    // 2. Attendance Today
    $attendanceData = $pdo->prepare("SELECT status, COUNT(*) as count FROM attendance WHERE date = ? GROUP BY status");
    $attendanceData->execute([$today]);
    $attStats = ['มา' => 0, 'ขาด' => 0, 'สาย' => 0, 'ลา' => 0, 'ป่วย' => 0];
    foreach ($attendanceData->fetchAll() as $row) {
        $attStats[$row['status']] = (int)$row['count'];
    }
    
    // 3. Gender breakdown of Present students
    $genderData = $pdo->prepare("
        SELECT s.gender, COUNT(*) as count 
        FROM students s 
        JOIN attendance a ON s.id = a.student_id 
        WHERE a.date = ? AND a.status = 'มา' 
        GROUP BY s.gender
    ");
    $genderData->execute([$today]);
    $presentGender = ['ชาย' => 0, 'หญิง' => 0];
    foreach ($genderData->fetchAll() as $row) {
        $gender = $row['gender'] == 'ชาย' ? 'ชาย' : 'หญิง';
        $presentGender[$gender] += (int)$row['count'];
    }

    // 4. Room Attendance (Top 10)
    $roomData = $pdo->prepare("
        SELECT s.room, 
               COUNT(*) as total,
               SUM(CASE WHEN a.status = 'มา' THEN 1 ELSE 0 END) as present
        FROM students s
        LEFT JOIN attendance a ON s.id = a.student_id AND a.date = ?
        GROUP BY s.room
        ORDER BY s.room ASC
        LIMIT 10
    ");
    $roomData->execute([$today]);
    $roomStats = [];
    foreach ($roomData->fetchAll() as $row) {
        $percent = $row['total'] > 0 ? round(($row['present'] / $row['total']) * 100) : 0;
        $roomStats[] = [
            'room' => $row['room'],
            'percent' => $percent
        ];
    }

    // 5. Total Checked vs Total
    $checkedCount = $pdo->prepare("SELECT COUNT(DISTINCT student_id) FROM attendance WHERE date = ?");
    $checkedCount->execute([$today]);
    $totalChecked = (int)$checkedCount->fetchColumn();

    echo json_encode([
        'basic' => [
            'total_students' => (int)$totalStudents,
            'teachers' => (int)$totalTeachers,
            'classes' => (int)$totalClasses,
            'subjects' => (int)$totalSubjects,
            'ratio' => $studentTeacherRatio
        ],
        'attendance' => $attStats,
        'analytics' => [
            'total_checked' => $totalChecked,
            'present_gender' => $presentGender,
            'room_stats' => $roomStats
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
