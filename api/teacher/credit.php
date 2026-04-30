<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

$student_id = $data['student_id'];
$item_id = $data['item_id'] ?? null;
$type = $data['type']; // บวก or ลบ
$points = $data['points'];
$reason = $data['reason'] ?? ''; // item_name
$remark = $data['remark'] ?? '';
$date = $data['date'];
$recorded_by = $_SESSION['user_id'];

// Default semester/year
$semester = 1;
$academic_year = 2569;

try {
    // If item_id is missing but reason is provided, try to find it
    if (!$item_id && $reason) {
        $cat_name = ($type === 'ลบ') ? 'ตัด' : 'เติม';
        $stmt = $pdo->prepare("SELECT id FROM point_items WHERE item_name = ? LIMIT 1");
        $stmt->execute([$reason]);
        $item = $stmt->fetch();
        $item_id = $item ? $item['id'] : null;
    }

    // Adjust points sign
    $finalPoints = ($type === 'ลบ') ? -abs($points) : abs($points);

    $sql = "INSERT INTO point_transactions (student_id, item_id, points, remark, recorded_by, occurrence_date, semester, academic_year, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$student_id, $item_id, $finalPoints, $remark, $recorded_by, $date, $semester, $academic_year]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
