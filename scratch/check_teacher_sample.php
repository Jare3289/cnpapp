<?php
require_once 'config.php';
$t = $pdo->query("SELECT * FROM teachers LIMIT 1")->fetch(PDO::FETCH_ASSOC);
echo json_encode($t, JSON_PRETTY_PRINT);
?>
