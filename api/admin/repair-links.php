<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $count = 0;
    // 1. Link Teachers based on teacher_id (if username matches teacher_id)
    $stmt = $pdo->query("SELECT id, username FROM users WHERE role = 'teacher'");
    $users = $stmt->fetchAll();
    
    foreach ($users as $user) {
        $uid = $user['id'];
        $uname = $user['username'];
        
        // Try to find teacher with this teacher_id or email
        $stmtT = $pdo->prepare("UPDATE teachers SET user_id = ? WHERE teacher_id = ? OR email = ?");
        $stmtT->execute([$uid, $uname, $uname]);
        $count += $stmtT->rowCount();
    }

    echo json_encode(['success' => true, 'message' => "Linked $count teachers to their user accounts."]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
