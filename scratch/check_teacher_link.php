<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT u.id, u.username, u.role, t.id as teacher_id 
                     FROM users u 
                     LEFT JOIN teachers t ON u.id = t.user_id 
                     WHERE u.role = 'teacher' LIMIT 10");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
