<?php
$pdo = new PDO('mysql:host=127.0.0.1', 'root', '');
$pdo->exec('DROP DATABASE IF EXISTS attendance_system');
echo "Database attendance_system dropped successfully.\n";
?>
