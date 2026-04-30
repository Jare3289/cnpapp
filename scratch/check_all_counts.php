<?php
require_once 'config.php';
$t = $pdo->query("SELECT COUNT(*) FROM teachers")->fetchColumn();
$r = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$g = $pdo->query("SELECT COUNT(*) FROM grade_levels")->fetchColumn();
echo "Teachers: $t, Rooms: $r, Grades: $g\n";
?>
