<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$old_db = 'attendance_system';
$new_db = 'cnpapp_system';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating database $new_db...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$new_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    // Get all tables from old db
    $tables = $pdo->query("SHOW TABLES FROM `$old_db`")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        echo "Copying table $table...\n";
        // Create table structure
        $pdo->exec("CREATE TABLE IF NOT EXISTS `$new_db`.`$table` LIKE `$old_db`.`$table` ");
        // Copy data
        $pdo->exec("INSERT IGNORE INTO `$new_db`.`$table` SELECT * FROM `$old_db`.`$table` ");
    }

    echo "Migration complete successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
