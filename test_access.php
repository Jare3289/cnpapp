<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'attendance_system';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Testing access to attendance_system.users...\n";
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    echo "Count: " . $stmt->fetchColumn() . "\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
