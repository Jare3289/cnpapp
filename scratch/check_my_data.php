<?php
require_once 'config.php';
session_start();
echo "Session: " . json_encode($_SESSION) . "\n";
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $t = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Teacher Data: " . json_encode($t) . "\n";
}
?>
