<?php
require_once 'config.php';

$teachers = $pdo->query("SELECT id FROM teachers LIMIT 74")->fetchAll(PDO::FETCH_COLUMN);
$rooms = $pdo->query("SELECT id FROM rooms")->fetchAll(PDO::FETCH_COLUMN);

$stmt = $pdo->prepare("UPDATE rooms SET teacher_id = ? WHERE id = ?");

foreach ($rooms as $i => $roomId) {
    if (isset($teachers[$i])) {
        $stmt->execute([$teachers[$i], $roomId]);
    }
}
echo "Assigned " . count($rooms) . " teachers to rooms.";
?>
