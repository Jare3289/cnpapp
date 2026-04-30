<?php
require_once 'config.php';
echo "Teacher rooms:\n";
$rows = $pdo->query("SELECT id, first_name_th, room FROM teachers LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  id={$r['id']} name={$r['first_name_th']} room={$r['room']}\n";

echo "\nStudent grade_level samples:\n";
$rows = $pdo->query("SELECT student_id, room, grade_level FROM students LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo "  {$r['student_id']} room={$r['room']} grade={$r['grade_level']}\n";
?>
