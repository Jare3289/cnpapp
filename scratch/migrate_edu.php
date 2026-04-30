<?php
require_once 'config.php';

try {
    $pdo->exec("ALTER TABLE teachers ADD COLUMN education_history JSON DEFAULT NULL");
    echo "Successfully added education_history column.\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column education_history already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
