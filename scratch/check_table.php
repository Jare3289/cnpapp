<?php
require_once 'config.php';
$stmt = $pdo->query("SHOW TABLES LIKE 'system_settings'");
echo $stmt->rowCount() > 0 ? "EXISTS" : "MISSING";
