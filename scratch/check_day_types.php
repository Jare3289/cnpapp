<?php
require_once 'config.php';
$stmt = $pdo->query("SELECT DISTINCT day_type FROM academic_days");
$types = $stmt->fetchAll(PDO::FETCH_COLUMN);
echo "Values in day_type column:\n";
foreach ($types as $t) {
    echo "- " . ($t ?: "[empty]") . "\n";
}
?>
