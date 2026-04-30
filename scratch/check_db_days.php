<?php
require_once 'c:/xampp/htdocs/cnpapp/config.php';
$stmt = $pdo->query("SELECT date_val, activity, day_type FROM academic_days LIMIT 40");
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
