<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Forbidden']);
    exit;
}

$q = $_GET['q'] ?? '';
if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Search by student_id, nickname, first_name_th, last_name_th
    $query = "%$q%";
    $sql = "SELECT id, student_id, first_name_th, last_name_th, nickname, room, number_in_class, photo 
            FROM students 
            WHERE student_id LIKE ? 
               OR nickname LIKE ? 
               OR first_name_th LIKE ? 
               OR last_name_th LIKE ?
            LIMIT 15";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$query, $query, $query, $query]);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($students);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
