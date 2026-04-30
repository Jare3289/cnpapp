<?php
// api/me.php
header('Content-Type: application/json');
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$userData = [
    'id' => $user_id,
    'username' => $_SESSION['username'],
    'role' => $role
];

try {
    if ($role === 'teacher' || $role === 'admin') {
        $stmt = $pdo->prepare("SELECT prefix, first_name_th, last_name_th, academic_standing, position, photo, room, faculty FROM teachers WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($details) {
            $userData = array_merge($userData, $details);
        }
    } else if ($role === 'student') {
        $stmt = $pdo->prepare("SELECT prefix, first_name_th, last_name_th, student_id, photo, room, grade_level, number_in_class FROM students WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $details = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($details) {
            $userData = array_merge($userData, $details);
        }
    }
} catch (PDOException $e) {
    // Silently fail and just use basic session info if DB error
}

echo json_encode([
    'user' => $userData
]);
?>
