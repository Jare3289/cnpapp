<?php
require_once 'config.php';

// 1. Reset teacher_id in rooms
$pdo->exec("UPDATE rooms SET teacher_id = NULL");

// 2. Get all teachers who have a room assigned
$stmt = $pdo->query("SELECT id, room FROM teachers WHERE room IS NOT NULL AND room != ''");
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updateStmt = $pdo->prepare("UPDATE rooms SET teacher_id = ? WHERE classroom_code = ?");

$count = 0;
foreach ($teachers as $t) {
    // Try matching room directly with classroom_code
    // classroom_code might be 112, room might be 112
    $updateStmt->execute([$t['id'], $t['room']]);
    if ($updateStmt->rowCount() > 0) {
        $count++;
    } else {
        // Try cleaning the room string (e.g., if it's "ม.1/12" -> "112")
        // This is a common pattern in Thai school data
        $cleanRoom = preg_replace('/[^0-9]/', '', $t['room']);
        if (strlen($cleanRoom) == 3) {
            $updateStmt->execute([$t['id'], $cleanRoom]);
            if ($updateStmt->rowCount() > 0) $count++;
        }
    }
}

echo "Successfully matched $count teachers to their rooms based on the 'room' field in teachers database.";
?>
