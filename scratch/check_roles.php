<?php
require_once 'config.php';
try {
    $stmt = $pdo->query("SELECT u.username, u.role as role_enum, r.name as role_name 
                         FROM users u 
                         LEFT JOIN roles r ON u.role_id = r.id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($users, JSON_PRETTY_PRINT);
} catch (Exception $e) {
    echo $e->getMessage();
}
?>
