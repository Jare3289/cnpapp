<?php
require 'config.php';
function dumpTable(PDO $pdo, string $table): void {
    echo "--- $table ---\n";
    try {
        $q = $pdo->query("DESCRIBE $table");
        while($r = $q->fetch(PDO::FETCH_ASSOC)) {
            echo $r['Field'] . " (" . $r['Type'] . ")\n";
        }
    } catch(Exception $e) { echo "Table $table not found\n"; }
}
dumpTable($pdo, 'point_categories');
dumpTable($pdo, 'point_items');
dumpTable($pdo, 'point_transactions');
dumpTable($pdo, 'students');
?>
