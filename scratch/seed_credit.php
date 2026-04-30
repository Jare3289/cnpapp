<?php
require_once '../config.php';

try {
    $pdo->exec("TRUNCATE TABLE point_items");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec("TRUNCATE TABLE point_categories");
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    // Positive
    $pdo->exec("INSERT INTO point_categories (id, category_name, is_positive) VALUES (1, 'ด้านจิตสาธารณะ', 1)");
    $pdo->exec("INSERT INTO point_items (category_id, item_name, points) VALUES 
        (1, 'ช่วยเหลืองานโรงเรียน', 5),
        (1, 'ทำความสะอาดบริเวณโรงเรียน', 3),
        (1, 'เข้าร่วมกิจกรรมจิตอาสา', 10)");

    $pdo->exec("INSERT INTO point_categories (id, category_name, is_positive) VALUES (2, 'ด้านการเรียนและระเบียบวินัย', 1)");
    $pdo->exec("INSERT INTO point_items (category_id, item_name, points) VALUES 
        (2, 'แต่งกายเรียบร้อยตามระเบียบ', 5),
        (2, 'มาโรงเรียนทันเวลาสม่ำเสมอ', 10),
        (2, 'ได้รับรางวัล/เชิดชูเกียรติ', 20)");

    // Negative
    $pdo->exec("INSERT INTO point_categories (id, category_name, is_positive) VALUES (3, 'ความผิดวินัย - มาสาย/หนีเรียน', 0)");
    $pdo->exec("INSERT INTO point_items (category_id, item_name, points) VALUES 
        (3, 'มาสาย', -2),
        (3, 'หนีเรียน/ไม่เข้าเรียนบางคาบ', -5),
        (3, 'ออกนอกบริเวณโรงเรียนโดยไม่ได้รับอนุญาต', -10)");

    $pdo->exec("INSERT INTO point_categories (id, category_name, is_positive) VALUES (4, 'ความผิดวินัย - การแต่งกายและพฤติกรรม', 0)");
    $pdo->exec("INSERT INTO point_items (category_id, item_name, points) VALUES 
        (4, 'แต่งกายผิดระเบียบ', -2),
        (4, 'ทรงผมผิดระเบียบ', -2),
        (4, 'ทะเลาะวิวาท', -30),
        (4, 'สูบบุหรี่/บุหรี่ไฟฟ้า', -50)");

    echo "Seed Credit Data Success!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
