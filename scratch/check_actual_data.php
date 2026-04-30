<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM academic_days LIMIT 20");
echo json_encode($stmt->fetchAll(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
?>
