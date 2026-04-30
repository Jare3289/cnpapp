<?php
require 'config.php';

$sql = "ALTER TABLE subjects 
        ADD COLUMN IF NOT EXISTS department VARCHAR(100) NULL,
        ADD COLUMN IF NOT EXISTS academic_year VARCHAR(10) NULL,
        ADD COLUMN IF NOT EXISTS semester VARCHAR(10) NULL,
        ADD COLUMN IF NOT EXISTS room VARCHAR(50) NULL";

try {
    $pdo->exec($sql);
    echo "Table 'subjects' updated successfully.\n";
} catch (PDOException $e) {
    echo "Error updating table: " . $e->getMessage() . "\n";
}
?>
