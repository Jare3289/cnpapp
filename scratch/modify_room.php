<?php
require 'config.php';
$pdo->exec("ALTER TABLE subjects MODIFY COLUMN room TEXT");
echo "Updated room column to TEXT";
?>
