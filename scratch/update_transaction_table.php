<?php
require 'config.php';
try {
    $pdo->exec("ALTER TABLE point_transactions 
                ADD COLUMN IF NOT EXISTS occurrence_date DATE DEFAULT NULL, 
                ADD COLUMN IF NOT EXISTS semester INT DEFAULT 1, 
                ADD COLUMN IF NOT EXISTS academic_year INT DEFAULT 2569");
    echo "Table point_transactions updated successfully.\n";
} catch (Exception $e) {
    echo "Error updating table: " . $e->getMessage() . "\n";
}
?>
