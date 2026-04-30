<?php
require_once 'config.php';
$stmt = $pdo->query("DESC academic_days");
echo json_encode($stmt->fetchAll());
