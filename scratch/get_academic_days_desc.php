<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE academic_days");
echo json_encode($stmt->fetchAll());
?>
