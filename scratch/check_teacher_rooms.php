<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT id, first_name_th, last_name_th, room FROM teachers WHERE room IS NOT NULL AND room != '' LIMIT 10");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']}, Name: {$row['first_name_th']}, Room: {$row['room']}\n";
}
?>
