<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT grade_level, COUNT(*) as room_count FROM rooms GROUP BY grade_level");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
