<?php
require_once 'config.php';

$grades = [
    'มัธยมศึกษาปีที่ 1' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
    'มัธยมศึกษาปีที่ 2' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
    'มัธยมศึกษาปีที่ 3' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
    'มัธยมศึกษาปีที่ 4' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13],
    'มัธยมศึกษาปีที่ 5' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12],
    'มัธยมศึกษาปีที่ 6' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]
];

$pdo->exec("TRUNCATE TABLE rooms");
$stmt = $pdo->prepare("INSERT INTO rooms (classroom_code, location_code, building, floor, room_no, grade_level) VALUES (?, ?, ?, ?, ?, ?)");

foreach ($grades as $grade => $rooms) {
    foreach ($rooms as $room) {
        $code = ($room < 10) ? substr($grade, -1) . '0' . $room : substr($grade, -1) . $room;
        // Simple mapping for demo
        $building = substr($grade, -1);
        $floor = ceil($room / 4);
        $room_no = $room;
        $loc = $building . $floor . $room_no;
        $stmt->execute([$code, $loc, $building, $floor, $room_no, $grade]);
    }
}
echo "Seeded " . $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn() . " rooms.";
?>
