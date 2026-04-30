<?php
require 'config.php';
$q = $pdo->query("SHOW COLUMNS FROM departments");
echo json_encode($q->fetchAll(PDO::FETCH_ASSOC), JSON_PRETTY_PRINT);
?>
