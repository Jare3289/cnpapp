<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT DISTINCT room FROM students");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE);
?>
