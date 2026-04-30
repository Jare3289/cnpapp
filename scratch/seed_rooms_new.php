<?php
require_once 'config.php';

$grades = [
    'มัธยมศึกษาปีที่ 1' => 13,
    'มัธยมศึกษาปีที่ 2' => 13,
    'มัธยมศึกษาปีที่ 3' => 13,
    'มัธยมศึกษาปีที่ 4' => 13,
    'มัธยมศึกษาปีที่ 5' => 12,
    'มัธยมศึกษาปีที่ 6' => 13
];

$pdo->exec("TRUNCATE TABLE rooms");
$stmt = $pdo->prepare("INSERT INTO rooms (classroom_code, class_level, classroom_no, grade_level, location_code, building, floor, room_no) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

foreach ($grades as $grade => $count) {
    $level_num = substr($grade, -1);
    for ($i = 1; $i <= $count; $i++) {
        $classroom_no = str_pad($i, 2, '0', STR_PAD_LEFT);
        $classroom_code = $level_num . $classroom_no;
        
        // Randomish location mapping
        $building = ceil($i / 5);
        $floor = ceil(($i % 5 ?: 5) / 2);
        $room_seq = $i;
        $location_code = $building . $floor . str_pad($room_seq, 2, '0', STR_PAD_LEFT);
        
        $stmt->execute([
            $classroom_code,
            $level_num,
            $i, // classroom_no
            $grade,
            $location_code,
            $building,
            $floor,
            $room_seq // room_no (sequence in building)
        ]);
    }
}
echo "Seeded " . $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn() . " rooms with new schema.";
?>
