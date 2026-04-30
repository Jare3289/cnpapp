<?php
session_start();
if (!isset($_SESSION['counter'])) {
    $_SESSION['counter'] = 0;
}
$_SESSION['counter']++;
echo json_encode([
    'session_id' => session_id(),
    'counter' => $_SESSION['counter'],
    'user_id' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null
]);
?>
