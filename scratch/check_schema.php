<?php
require 'config.php';
function dumpTable($pdo, $table) {
    echo "--- $table ---\n";
    $q = $pdo->query("DESCRIBE $table");
    while($r = $q->fetch(PDO::FETCH_ASSOC)) {
        echo $r['Field'] . " (" . $r['Type'] . ")\n";
    }
}
dumpTable($pdo, 'students');
dumpTable($pdo, 'attendance');
?>
