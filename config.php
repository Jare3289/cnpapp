<?php
// config.php - ระบบสลับการเชื่อมต่ออัตโนมัติ (Local vs Production)

// ตรวจสอบว่ารันที่เครื่องตัวเอง (localhost) หรือบน Server จริง
if (PHP_SAPI === 'cli' || in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1']) || ($_SERVER['HTTP_HOST'] ?? '') == 'localhost') {
    // --- ตั้งค่าสำหรับเครื่องตัวเอง (XAMPP) ---
    $host = '127.0.0.1';
    $db   = 'cnpapp_system'; 
    $user = 'root';
    $pass = ''; 
} else {
    // --- ตั้งค่าสำหรับ Server จริง (InfinityFree) ---
    $host = 'sql308.infinityfree.com';
    $db   = 'if0_41789362_cnpapp'; 
    $user = 'if0_41789362';
    $pass = 'ik47Gv2CMCT1';
}

$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("ไม่สามารถเชื่อมต่อ MySQL ได้: " . $e->getMessage());
}
?>
