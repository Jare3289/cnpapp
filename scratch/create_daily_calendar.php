<?php
require 'api/config.php';
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS day_types (
        type_id VARCHAR(20) PRIMARY KEY,
        type_name VARCHAR(100),
        color_code VARCHAR(10)
    )");

    $pdo->exec("INSERT IGNORE INTO day_types VALUES 
        ('reg', 'วันเรียนปกติ', '#FFFFFF'),
        ('sat', 'วันเสาร์', '#fb923c'),
        ('sun', 'วันอาทิตย์', '#dc2626'),
        ('hol', 'วันหยุดราชการ', '#f9a8d4'),
        ('activity', 'กิจกรรมโรงเรียน', '#93c5fd'),
        ('empty', 'ปิดภาคเรียน', '#e5e7eb')
    ");

    $pdo->exec("CREATE TABLE IF NOT EXISTS calendar_days (
        date_id DATE PRIMARY KEY,
        academic_year INT(4),
        semester INT(1),
        day_type VARCHAR(20),
        description VARCHAR(255),
        FOREIGN KEY (day_type) REFERENCES day_types(type_id)
    )");

    // Since `holidays` has custom data, let's just make sure we have a script to populate from 1st April to 31st March next year
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
