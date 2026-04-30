<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("DESCRIBE teachers");
    $cols = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach($cols as $col) {
        echo $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
