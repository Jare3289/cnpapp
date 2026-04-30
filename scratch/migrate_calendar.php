<?php
require_once 'config.php';

try {
    // 1. Update academic_days table
    $pdo->exec("ALTER TABLE academic_days 
        ADD COLUMN IF NOT EXISTS location VARCHAR(255) DEFAULT '',
        ADD COLUMN IF NOT EXISTS category VARCHAR(50) DEFAULT 'ทั่วไป',
        ADD COLUMN IF NOT EXISTS color VARCHAR(20) DEFAULT '#4f6ef7',
        ADD COLUMN IF NOT EXISTS all_day TINYINT(1) DEFAULT 1,
        ADD COLUMN IF NOT EXISTS is_holiday TINYINT(1) DEFAULT 0,
        ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");

    // 2. Create system_settings table
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");

    // Initial settings
    $stmt = $pdo->prepare("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES (?, ?)");
    $stmt->execute(['school_name', 'โรงเรียนชัยนาทพิทยาคม']);

    echo "Migration Success!";
} catch (Exception $e) {
    echo "Migration Error: " . $e->getMessage();
}
