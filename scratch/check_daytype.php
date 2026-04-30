<?php
require_once 'config.php';

// Simulate admin session
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';
$_SESSION['username'] = 'admin';

$stmt = $pdo->query("SELECT id, date_val, activity, day_type FROM academic_days ORDER BY date_val ASC LIMIT 10");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "=== SAMPLE DATA FROM academic_days ===\n";
foreach ($rows as $r) {
    echo sprintf("%-12s | %-30s | %s\n", $r['date_val'], $r['activity'], $r['day_type']);
}

echo "\n=== DISTINCT day_type VALUES ===\n";
$types = $pdo->query("SELECT DISTINCT day_type, COUNT(*) as cnt FROM academic_days GROUP BY day_type ORDER BY cnt DESC")->fetchAll(PDO::FETCH_ASSOC);
foreach ($types as $t) {
    echo sprintf("%-20s : %d rows\n", $t['day_type'], $t['cnt']);
}
?>
