<?php
require_once 'config.php';
$stmt = $pdo->query("DESCRIBE teachers");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
