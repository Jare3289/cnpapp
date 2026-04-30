<?php
$hosts = ['localhost', '127.0.0.1', '::1'];
foreach ($hosts as $h) {
    echo "Testing host: $h\n";
    try {
        $pdo = new PDO("mysql:host=$h", "root", "");
        echo "Successfully connected to $h as root\n";
    } catch (Exception $e) {
        echo "Failed to connect to $h: " . $e->getMessage() . "\n";
    }
}
?>
