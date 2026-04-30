<?php
require 'config.php';
$q = $pdo->query("SHOW COLUMNS FROM subjects");
echo json_encode($q->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
