<?php
// api/teacher/my-classes.php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['classes' => []]);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$type = $_GET['type'] ?? '';

try {
    if ($role === 'admin' || $type !== 'daily') {
        // Admins or Subject Attendance: show all rooms
        $stmt = $pdo->query("SELECT DISTINCT room FROM students WHERE room IS NOT NULL AND room != '' ORDER BY CAST(room AS UNSIGNED) ASC, room ASC");
        $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        // Daily Attendance for Teachers: only their advisory room
        $stmt = $pdo->prepare("SELECT room FROM teachers WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $teacher_room = $stmt->fetchColumn();
        
        $classes = $teacher_room ? [$teacher_room] : [];
    }
    
    echo json_encode(['success' => true, 'classes' => $classes]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'classes' => [], 'error' => $e->getMessage()]);
}
?>
