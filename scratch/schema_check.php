<?php
require_once 'config.php';
echo "--- students (sample columns) ---\n";
$stmt = $pdo->query("DESCRIBE students");
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) echo $c['Field'] . " (" . $c['Type'] . ")\n";
echo "\n--- teachers (sample columns) ---\n";
$stmt = $pdo->query("DESCRIBE teachers");
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) echo $c['Field'] . " (" . $c['Type'] . ")\n";
echo "\n--- users ---\n";
$stmt = $pdo->query("DESCRIBE users");
foreach($stmt->fetchAll(PDO::FETCH_ASSOC) as $c) echo $c['Field'] . " (" . $c['Type'] . ")\n";
?>
