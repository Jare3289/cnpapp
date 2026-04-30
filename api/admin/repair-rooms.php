<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

function convertRoomFormat($roomStr) {
    if (!$roomStr) return $roomStr;
    if (preg_match('/^[1-6]\d{2}$/', trim($roomStr))) return trim($roomStr);
    
    preg_match_all('/\d+/', $roomStr, $matches);
    if (isset($matches[0]) && count($matches[0]) >= 2) {
        $grade = $matches[0][0];
        $room = $matches[0][1];
        if ($grade >= 1 && $grade <= 6) {
            return $grade . str_pad($room, 2, '0', STR_PAD_LEFT);
        }
    }
    return trim($roomStr);
}

try {
    $tables = ['teachers', 'students'];
    $totalUpdated = 0;
    
    $pdo->beginTransaction();
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT id, room FROM $table WHERE room IS NOT NULL AND room != ''");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $updateStmt = $pdo->prepare("UPDATE $table SET room = ? WHERE id = ?");
        
        foreach ($rows as $row) {
            $oldRoom = $row['room'];
            $newRoom = convertRoomFormat($oldRoom);
            if ($oldRoom !== $newRoom) {
                $updateStmt->execute([$newRoom, $row['id']]);
                $totalUpdated++;
            }
        }
    }
    
    $pdo->commit();
    echo json_encode(['success' => true, 'message' => "อัปเดตรูปแบบห้องเรียนเรียบร้อยแล้ว จำนวน $totalUpdated รายการ"]);
    
} catch (PDOException $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
