<?php
require_once 'config.php';
echo "Rooms: " . $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn() . "\n";
echo "Grades: " . $pdo->query("SELECT COUNT(*) FROM grade_levels")->fetchColumn() . "\n";
?>
