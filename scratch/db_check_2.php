<?php
require_once 'config.php';
echo "--- point_transactions ---\n";
$stmt = $pdo->query("DESCRIBE point_transactions");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
?>
