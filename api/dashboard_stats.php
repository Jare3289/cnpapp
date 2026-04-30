<?php
// api/dashboard_stats.php
header('Content-Type: application/json');
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

try {
    // Basic stats
    $students = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
    $teachers = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
    $classes = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();

    $today = date('Y-m-d');
    $present = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'มา'");
    $present->execute([$today]);
    
    $absent = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'ขาด'");
    $absent->execute([$today]);

    $late = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'สาย'");
    $late->execute([$today]);

    $leave = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ? AND status = 'ลา'");
    $leave->execute([$today]);

    echo json_encode([
        'students' => (int)$students,
        'teachers' => (int)$teachers,
        'classes' => (int)$classes,
        'today' => [
            'present' => (int)$present->fetchColumn(),
            'absent' => (int)$absent->fetchColumn(),
            'late' => (int)$late->fetchColumn(),
            'leave' => (int)$leave->fetchColumn()
        ]
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
