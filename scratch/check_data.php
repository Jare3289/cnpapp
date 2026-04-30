<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT * FROM academic_days LIMIT 5");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
