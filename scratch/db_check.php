<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE point_transactions");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
$stmt = $pdo->query("DESCRIBE users");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
