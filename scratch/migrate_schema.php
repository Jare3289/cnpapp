<?php
require_once 'config.php';
try {
    // 1. Add nickname if not exists
    $pdo->exec("ALTER TABLE teachers ADD COLUMN IF NOT EXISTS nickname VARCHAR(50) AFTER last_name_en");
    
    // 2. Rename current_address_* to address_* to match the code
    // Check if current_address_no exists
    $stmt = $pdo->query("SHOW COLUMNS FROM teachers LIKE 'current_address_no'");
    if ($stmt->fetch()) {
        $pdo->exec("ALTER TABLE teachers CHANGE current_address_no address_no VARCHAR(50)");
        $pdo->exec("ALTER TABLE teachers CHANGE current_address_moo address_moo VARCHAR(20)");
        $pdo->exec("ALTER TABLE teachers CHANGE current_address_soi address_soi VARCHAR(100)");
        $pdo->exec("ALTER TABLE teachers CHANGE current_address_road address_road VARCHAR(100)");
        $pdo->exec("ALTER TABLE teachers CHANGE current_address_subdistrict address_subdistrict VARCHAR(100)");
        $pdo->exec("ALTER TABLE teachers CHANGE current_address_district address_district VARCHAR(100)");
        $pdo->exec("ALTER TABLE teachers CHANGE current_address_province address_province VARCHAR(100)");
        $pdo->exec("ALTER TABLE teachers CHANGE current_address_zipcode address_zipcode VARCHAR(10)");
    }
    
    echo "Schema updated successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
