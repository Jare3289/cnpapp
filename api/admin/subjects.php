<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $pdo->prepare("SELECT s.*, t.prefix, t.first_name_th, t.last_name_th 
                                   FROM subjects s 
                                   LEFT JOIN teachers t ON s.teacher_id = t.user_id 
                                   WHERE s.id = ?");
            $stmt->execute([$id]);
            $subject = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'data' => $subject]);
        } else {
            $stmt = $pdo->query("SELECT s.*, t.prefix, t.first_name_th, t.last_name_th 
                                 FROM subjects s 
                                 LEFT JOIN teachers t ON s.teacher_id = t.user_id 
                                 ORDER BY s.subject_code ASC");
            $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['success' => true, 'subjects' => $subjects]);
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) $data = $_POST;

        $subject_code = $data['subject_code'] ?? '';
        $subject_name = $data['subject_name'] ?? '';
        $teacher_id = $data['teacher_id'] ?? null;
        $academic_year = $data['academic_year'] ?? '';
        $semester = $data['semester'] ?? '';
        $department = $data['department'] ?? '';
        $room_raw = $data['room'] ?? '';
        $room = is_array($room_raw) ? implode(',', $room_raw) : $room_raw;
        $periods_raw = $data['periods'] ?? '';
        $periods = is_array($periods_raw) ? implode(',', $periods_raw) : $periods_raw;

        if (!$subject_code || !$subject_name) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO subjects (subject_code, subject_name, teacher_id, academic_year, semester, department, room, periods) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$subject_code, $subject_name, $teacher_id, $academic_year, $semester, $department, $room, $periods]);
        echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลวิชาสำเร็จ']);

    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        $subject_code = $data['subject_code'] ?? '';
        $subject_name = $data['subject_name'] ?? '';
        $teacher_id = $data['teacher_id'] ?? null;
        $academic_year = $data['academic_year'] ?? '';
        $semester = $data['semester'] ?? '';
        $department = $data['department'] ?? '';
        $room_raw = $data['room'] ?? '';
        $room = is_array($room_raw) ? implode(',', $room_raw) : $room_raw;
        $periods_raw = $data['periods'] ?? '';
        $periods = is_array($periods_raw) ? implode(',', $periods_raw) : $periods_raw;

        if (!$id || !$subject_code || !$subject_name) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE subjects SET subject_code = ?, subject_name = ?, teacher_id = ?, academic_year = ?, semester = ?, department = ?, room = ?, periods = ? WHERE id = ?");
        $stmt->execute([$subject_code, $subject_name, $teacher_id, $academic_year, $semester, $department, $room, $periods, $id]);
        echo json_encode(['success' => true, 'message' => 'อัปเดตข้อมูลวิชาสำเร็จ']);

    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Missing ID']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'ลบข้อมูลวิชาสำเร็จ']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
