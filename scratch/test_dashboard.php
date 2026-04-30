<?php
require_once 'config.php';
// Test dashboard API as teacher
$room = '1/1'; // example teacher room
$where = ["s.room = ?"];
$params = [$room];
$txWhereAnd = "WHERE s.room = ? AND ";

$sqlCat = "SELECT c.category_name as label, COUNT(*) as value
           FROM point_transactions t
           JOIN point_items i ON t.item_id = i.id
           JOIN point_categories c ON i.category_id = c.id
           JOIN students s ON t.student_id = s.id
           {$txWhereAnd} c.is_positive = 0
           GROUP BY c.id ORDER BY value DESC LIMIT 5";
try {
    $stmt = $pdo->prepare($sqlCat);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Categories query OK, rows: " . count($rows) . "\n";

    // Test student table query
    $tableWhereSql = "WHERE s.room = ?";
    $sqlTable = "SELECT s.student_id, s.number_in_class as number, s.first_name_th, s.last_name_th, s.room, s.grade_level,
                    COALESCE(SUM(CASE WHEN t.points > 0 THEN t.points ELSE 0 END), 0) as plus_points,
                    COALESCE(SUM(CASE WHEN t.points < 0 THEN ABS(t.points) ELSE 0 END), 0) as minus_points,
                    (100 + COALESCE(SUM(t.points), 0)) as remaining_score
                 FROM students s
                 LEFT JOIN point_transactions t ON s.id = t.student_id
                 $tableWhereSql
                 GROUP BY s.id
                 ORDER BY s.number_in_class ASC LIMIT 5";
    $stmt = $pdo->prepare($sqlTable);
    $stmt->execute([$room]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "Student table query OK, rows: " . count($rows) . "\n";
    foreach ($rows as $r) echo "  {$r['student_id']} {$r['first_name_th']} score={$r['remaining_score']}\n";

    // Test what rooms exist
    $rooms = $pdo->query("SELECT DISTINCT room FROM students WHERE is_active = 1 ORDER BY room LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
    echo "\nAvailable rooms: ";
    echo implode(', ', array_column($rooms, 'room')) . "\n";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>
