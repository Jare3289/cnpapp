<?php
require '../config.php';
try {
    $pdo->exec("ALTER TABLE holidays ADD COLUMN description TEXT DEFAULT NULL");
    echo "Added description column.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "description column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
try {
    $pdo->exec("ALTER TABLE holidays MODIFY COLUMN type ENUM('holiday', 'special', 'compensatory', 'activity') NOT NULL DEFAULT 'holiday'");
    echo "Modified type column to support 'activity'.\n";
} catch (Exception $e) {
    echo "Error modifying type: " . $e->getMessage() . "\n";
}
?>
