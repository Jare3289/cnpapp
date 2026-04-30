<?php
// api/public_service.php
header('Content-Type: application/json');
require_once '../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        handleGet($pdo);
        break;
    case 'PUT':
        handlePut($pdo);
        break;
    case 'DELETE':
        handleDelete($pdo);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method Not Allowed']);
}

function handleGet($pdo) {
    $year = $_GET['year'] ?? '';
    $semester = $_GET['semester'] ?? '';
    $status = $_GET['status'] ?? '';
    $search = $_GET['search'] ?? '';

    $where = "1=1";
    $params = [];

    if ($year) { $where .= " AND r.academic_year = ?"; $params[] = $year; }
    if ($semester) { $where .= " AND r.semester = ?"; $params[] = $semester; }
    if ($status) { $where .= " AND r.status = ?"; $params[] = $status; }
    if ($search) {
        $where .= " AND (s.first_name_th LIKE ? OR s.last_name_th LIKE ? OR s.student_id LIKE ? OR r.activity_name LIKE ?)";
        $s = "%$search%";
        $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
    }

    try {
        // Fetch Records
        $sql = "SELECT r.*, s.first_name_th, s.last_name_th, s.room, 
                       (SELECT CONCAT(first_name_th, ' ', last_name_th) FROM teachers t WHERE t.user_id = r.approver_id) as approver_name
                FROM public_service_records r
                JOIN students s ON r.student_id = s.student_id
                WHERE $where
                ORDER BY r.activity_date DESC, r.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Add formatted date
        foreach ($records as &$rec) {
            $date = new DateTime($rec['activity_date']);
            $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
            $rec['activity_date_th'] = $date->format('j') . ' ' . $months[(int)$date->format('m')] . ' ' . ($date->format('y') + 12); // Short year in BE
        }

        // Fetch Stats
        $statsSql = "SELECT 
                        COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved_count,
                        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                        COUNT(DISTINCT student_id) as total_students
                     FROM public_service_records r
                     WHERE academic_year = ? AND semester = ?";
        $statsStmt = $pdo->prepare($statsSql);
        $statsStmt->execute([$year, $semester]);
        $stats = $statsStmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true,
            'records' => $records,
            'stats' => $stats
        ]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handlePut($pdo) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['id']) || !isset($data['status'])) {
        echo json_encode(['error' => 'Invalid data']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE public_service_records SET status = ?, approver_id = ? WHERE id = ?");
        $stmt->execute([$data['status'], $_SESSION['user_id'], $data['id']]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function handleDelete($pdo) {
    $id = $_GET['id'] ?? '';
    if (!$id) {
        echo json_encode(['error' => 'Missing ID']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM public_service_records WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}
?>
