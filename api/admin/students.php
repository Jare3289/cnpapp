<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$classroom = $_GET['classroom'] ?? null;

try {
    if ($classroom) {
        $stmt = $pdo->prepare("SELECT s.*, u.username FROM students s LEFT JOIN users u ON s.user_id = u.id WHERE s.room = ? ORDER BY CAST(s.number_in_class AS UNSIGNED) ASC, s.student_id ASC");
        $stmt->execute([$classroom]);
    } else {
        $stmt = $pdo->query("SELECT s.*, u.username FROM students s LEFT JOIN users u ON s.user_id = u.id ORDER BY s.grade_level, s.room, CAST(s.number_in_class AS UNSIGNED) ASC, s.student_id ASC");
    }
    
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'students' => $students]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>

