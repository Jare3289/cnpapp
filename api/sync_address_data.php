<?php
require_once __DIR__ . '/../config.php';

try {
    // 1. Fetch JSON data
    $url = 'https://raw.githubusercontent.com/kongvut/thai-province-data/refs/heads/master/api/latest/province_with_district_and_sub_district.json';
    $json = file_get_contents($url);
    $data = json_decode($json, true);

    if (!$data) {
        die("Failed to fetch or decode JSON data from " . $url);
    }

    // 2. Prepare statements
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0; TRUNCATE provinces; TRUNCATE districts; TRUNCATE subdistricts; SET FOREIGN_KEY_CHECKS = 1;");
    
    $stmtProv = $pdo->prepare("INSERT INTO provinces (id, name) VALUES (?, ?)");
    $stmtDist = $pdo->prepare("INSERT INTO districts (id, name, province_id) VALUES (?, ?, ?)");
    $stmtSub = $pdo->prepare("INSERT INTO subdistricts (id, postcode, name, district_id) VALUES (?, ?, ?, ?)");

    $pdo->beginTransaction();

    foreach ($data as $province) {
        $stmtProv->execute([$province['id'], $province['name_th']]);
        
        if (isset($province['districts'])) {
            foreach ($province['districts'] as $dist) {
                $stmtDist->execute([$dist['id'], $dist['name_th'], $province['id']]);
                
                if (isset($dist['sub_districts'])) {
                    foreach ($dist['sub_districts'] as $sub) {
                        $stmtSub->execute([$sub['id'], (int)$sub['zip_code'], $sub['name_th'], $dist['id']]);
                    }
                }
            }
        }
    }

    $pdo->commit();
    echo "Sync Successful! Processed " . count($data) . " provinces.";

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
