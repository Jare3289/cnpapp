<?php
require_once 'config.php';
$stmt = $pdo->query("SHOW COLUMNS FROM teachers LIKE 'signature'");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
?>
