<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT t.id, t.user_id, u.username, u.role, t.first_name_th, t.last_name_th 
                     FROM teachers t 
                     JOIN users u ON t.user_id = u.id 
                     LIMIT 20");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
