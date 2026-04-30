<?php
require 'config.php';

try {
    // Rename columns if they exist
    $pdo->exec("ALTER TABLE subjects CHANGE COLUMN IF EXISTS code subject_code VARCHAR(20) NOT NULL");
    $pdo->exec("ALTER TABLE subjects CHANGE COLUMN IF EXISTS name subject_name VARCHAR(255) NOT NULL");
    
    // Ensure all requested columns exist
    $cols = [
        'department' => "VARCHAR(100) NULL",
        'academic_year' => "VARCHAR(10) NULL",
        'semester' => "VARCHAR(10) NULL",
        'room' => "VARCHAR(50) NULL"
    ];
    
    foreach ($cols as $col => $type) {
        $pdo->exec("ALTER TABLE subjects ADD COLUMN IF NOT EXISTS $col $type");
    }

    echo "Subjects table structure updated successfully.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
