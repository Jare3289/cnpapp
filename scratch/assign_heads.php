<?php
require_once 'config.php';

$teachers = $pdo->query("SELECT id FROM teachers ORDER BY id DESC LIMIT 6")->fetchAll(PDO::FETCH_COLUMN);
$grades = $pdo->query("SELECT id FROM grade_levels")->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("UPDATE grade_levels SET head_teacher_id = ? WHERE id = ?");

foreach ($grades as $i => $gradeId) {
    if (isset($teachers[$i])) {
        $stmt->execute([$teachers[$i], $gradeId]);
    }
}
echo "Assigned " . count($grades) . " head teachers.";
?>
