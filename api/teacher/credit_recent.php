<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['teacher', 'admin'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // Fetch recent 50 transactions with student and teacher details
    $sql = "SELECT 
                t.id, 
                t.points, 
                t.remark, 
                t.created_at,
                s.student_id,
                s.first_name_th,
                s.last_name_th,
                s.room,
                i.item_name as reason,
                u.username as teacher_name
            FROM point_transactions t
            JOIN students s ON t.student_id = s.id
            JOIN users u ON t.recorded_by = u.id
            LEFT JOIN point_items i ON t.item_id = i.id
            ORDER BY t.created_at DESC
            LIMIT 50";
            
    $stmt = $pdo->query($sql);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format names
    foreach ($items as &$item) {
        $item['student_name'] = $item['first_name_th'] . ' ' . $item['last_name_th'];
    }

    echo json_encode(['success' => true, 'items' => $items]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
