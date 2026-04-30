<?php
require_once 'config.php';
try {
    $cols = [
        "category" => "VARCHAR(100) DEFAULT 'ทั่วไป'",
        "day_type" => "VARCHAR(50) DEFAULT 'ปกติ'",
        "color" => "VARCHAR(20) DEFAULT '#4f6ef7'",
        "location" => "VARCHAR(255) DEFAULT ''",
        "all_day" => "TINYINT(1) DEFAULT 1",
        "created_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP",
        "updated_at" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP"
    ];

    foreach ($cols as $col => $def) {
        try {
            $pdo->exec("ALTER TABLE academic_days ADD COLUMN $col $def");
            echo "Added $col\n";
        } catch(Exception $e) {
            echo "Skipped $col (maybe exists)\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
