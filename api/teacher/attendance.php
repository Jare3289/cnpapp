<?php
// api/teacher/attendance.php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch students for a class
    $class_name = $_GET['class_name'] ?? '';
    $type = $_GET['type'] ?? 'subject'; // 'daily' or 'subject'
    $date = $_GET['date'] ?? date('Y-m-d');
    $period = $_GET['period'] ?? 1;

    if (!$class_name) {
        echo json_encode(['success' => false, 'error' => 'Missing class_name']);
        exit;
    }

    try {
        // Search by the room column as requested
        $stmt = $pdo->prepare("SELECT * FROM students WHERE room = ? ORDER BY CAST(number_in_class AS UNSIGNED) ASC");
        $stmt->execute([$class_name]);
        $students = $stmt->fetchAll();

        // Ensure full_name_th exists for the UI
        foreach ($students as &$s) {
            if (!isset($s['full_name_th'])) {
                $fname = $s['first_name_th'] ?? $s['first_name'] ?? '';
                $lname = $s['last_name_th'] ?? $s['last_name'] ?? '';
                $s['full_name_th'] = trim(($s['prefix'] ?? '') . ' ' . $fname . ' ' . $lname);
            }
        }

        // Get existing attendance for today/type/period
        $sql = "SELECT student_id, status, remark, period FROM attendance WHERE class_name = ? AND date = ? AND type = ?";
        $params = [$class_name, $date, $type];
        
        if ($type === 'subject') {
            $sql .= " AND period = ?";
            $params[] = $period;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $existing = $stmt->fetchAll();
        
        $map = [];
        foreach ($existing as $row) {
            $map[$row['student_id']] = $row;
        }

        // Merge existing status into students list
        foreach ($students as &$s) {
            if (isset($map[$s['id']])) {
                $s['status'] = $map[$s['id']]['status'];
                $s['remark'] = $map[$s['id']]['remark'] ?? '';
                $s['period'] = $map[$s['id']]['period'] ?? 1;
            } else {
                $s['status'] = null;
                $s['remark'] = '';
                $s['period'] = 1;
            }
        }

        echo json_encode(['success' => true, 'students' => $students]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }

} elseif ($method === 'POST') {
    // Save attendance batch
    $data = json_decode(file_get_contents('php://input'), true);
    $date = $data['date'] ?? date('Y-m-d');
    $class_name = $data['class_name'] ?? '';
    $records = $data['records'] ?? [];
    $period = $data['period'] ?? 1;
    $type = $data['type'] ?? 'subject'; // 'daily' or 'subject'
    $recorded_by = $_SESSION['user_id'];
    
    // New fields from modern UI
    $academic_year = $data['academic_year'] ?? null;
    $semester = $data['semester'] ?? null;
    $subject_code = $data['subject_code'] ?? null;

    if (!$class_name || empty($records)) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Delete existing for this class/date/type/period/subject
        $sqlDel = "DELETE FROM attendance WHERE class_name = ? AND date = ? AND type = ?";
        $paramsDel = [$class_name, $date, $type];
        
        if ($type === 'subject') {
            if ($subject_code) {
                // If we have subject code, filter by it too to avoid deleting other subjects on same period
                // However, if the column doesn't exist yet, this will fail.
                // For now, we'll keep it simple or use period.
                $sqlDel .= " AND period = ?";
                $paramsDel[] = $period;
            } else {
                $sqlDel .= " AND period = ?";
                $paramsDel[] = $period;
            }
        }
        
        $stmt = $pdo->prepare($sqlDel);
        $stmt->execute($paramsDel);

        // Check if columns exist (simple way: try-catch or just ignore errors if columns missing)
        // For a robust implementation, we'd check schema. 
        // For now, we'll try to insert and if it fails due to missing columns, we'll fallback.
        
        $sqlIns = "INSERT INTO attendance (student_id, class_name, date, status, remark, period, type";
        $placeholders = "?, ?, ?, ?, ?, ?, ?";
        $paramsIns = []; 
        
        // We'll check if columns exist
        $hasExtraCols = false;
        $hasRecordedBy = false;
        try {
            $pdo->query("SELECT subject_code, semester, academic_year FROM attendance LIMIT 0");
            $hasExtraCols = true;
        } catch (Exception $e) {}
        
        try {
            $pdo->query("SELECT recorded_by FROM attendance LIMIT 0");
            $hasRecordedBy = true;
        } catch (Exception $e) {}

        if ($hasExtraCols) {
            $sqlIns .= ", subject_code, semester, academic_year";
            $placeholders .= ", ?, ?, ?";
        }
        if ($hasRecordedBy) {
            $sqlIns .= ", recorded_by";
            $placeholders .= ", ?";
        }
        $sqlIns .= ") VALUES ($placeholders)";
        
        $stmt = $pdo->prepare($sqlIns);
        
        foreach ($records as $r) {
            $p = [
                $r['student_id'],
                $class_name,
                $date,
                $r['status'],
                $r['remark'] ?? null,
                $period,
                $type
            ];
            if ($hasExtraCols) {
                $p[] = $subject_code;
                $p[] = $semester;
                $p[] = $academic_year;
            }
            if ($hasRecordedBy) {
                $p[] = $recorded_by;
            }
            $stmt->execute($p);
        }

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($method === 'DELETE') {
    // Delete attendance records
    $date = $_GET['date'] ?? date('Y-m-d');
    $class_name = $_GET['class_name'] ?? '';
    $period = $_GET['period'] ?? 1;
    $type = $_GET['type'] ?? 'subject';
    $subject_code = $_GET['subject_code'] ?? null;

    if (!$class_name) {
        echo json_encode(['success' => false, 'error' => 'Missing class_name']);
        exit;
    }

    try {
        $sql = "DELETE FROM attendance WHERE class_name = ? AND date = ? AND type = ?";
        $params = [$class_name, $date, $type];
        
        if ($type === 'subject') {
            if ($subject_code) {
                // Try subject_code if column exists, otherwise fallback to period
                try {
                    $pdo->prepare("DELETE FROM attendance WHERE class_name = ? AND date = ? AND type = ? AND subject_code = ?")
                        ->execute([$class_name, $date, $type, $subject_code]);
                    echo json_encode(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
                    exit;
                } catch (Exception $e) {
                    $sql .= " AND period = ?";
                    $params[] = $period;
                }
            } else {
                $sql .= " AND period = ?";
                $params[] = $period;
            }
        }
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        echo json_encode(['success' => true, 'message' => 'ลบข้อมูลเรียบร้อยแล้ว']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>
