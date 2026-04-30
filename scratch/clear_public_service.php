<?php
require_once 'config.php';
try {
    $pdo->exec("TRUNCATE TABLE public_service_records");
    echo "Successfully cleared public_service_records table.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
