<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'teacher'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$role = $_SESSION['role'];
$userId = $_SESSION['user_id'];

// Determine scope: teacher sees only their room, admin can filter
$forceRoom = '';
$forceGrade = '';

if ($role === 'teacher') {
    // Get teacher's room from teachers table
    $stmt = $pdo->prepare("SELECT t.room, t.first_name_th, t.last_name_th FROM teachers t JOIN users u ON u.role_id = t.id WHERE u.id = ? LIMIT 1");
    $stmt->execute([$userId]);
    $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($teacher && $teacher['room']) {
        $forceRoom = $teacher['room'];
    }
}

// If admin, allow URL filter params; teacher is locked to their room
$grade = ($role === 'admin') ? ($_GET['grade'] ?? '') : $forceGrade;
$room  = ($role === 'teacher') ? $forceRoom : ($_GET['room'] ?? '');

try {
    $where = [];
    $params = [];

    if ($grade) {
        $where[] = "s.grade_level = ?";
        $params[] = $grade;
    }
    if ($room) {
        $where[] = "s.room = ?";
        $params[] = $room;
    }

    // Build WHERE for transaction-joined queries (need special handling for AND/WHERE)
    $hasFilter = !empty($where);
    $txWhere = $hasFilter ? "WHERE " . implode(" AND ", $where) : "";
    $txWhereAnd = $hasFilter ? "WHERE " . implode(" AND ", $where) . " AND " : "WHERE ";

    // 1. Top Violation Categories (Donut)
    $sqlCat = "SELECT c.category_name as label, COUNT(*) as value
               FROM point_transactions t
               JOIN point_items i ON t.item_id = i.id
               JOIN point_categories c ON i.category_id = c.id
               JOIN students s ON t.student_id = s.id
               {$txWhereAnd} c.is_positive = 0
               GROUP BY c.id ORDER BY value DESC LIMIT 5";
    $stmt = $pdo->prepare($sqlCat);
    $stmt->execute($params);
    $topCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Most Frequent Negative Behaviors (Bar)
    $sqlNeg = "SELECT i.item_name as label, COUNT(*) as value
               FROM point_transactions t
               JOIN point_items i ON t.item_id = i.id
               JOIN students s ON t.student_id = s.id
               {$txWhereAnd} t.points < 0
               GROUP BY i.id ORDER BY value DESC LIMIT 5";
    $stmt = $pdo->prepare($sqlNeg);
    $stmt->execute($params);
    $topNegative = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Most Frequent Positive Behaviors (Bar)
    $sqlPos = "SELECT i.item_name as label, COUNT(*) as value
               FROM point_transactions t
               JOIN point_items i ON t.item_id = i.id
               JOIN students s ON t.student_id = s.id
               {$txWhereAnd} t.points > 0
               GROUP BY i.id ORDER BY value DESC LIMIT 5";
    $stmt = $pdo->prepare($sqlPos);
    $stmt->execute($params);
    $topPositive = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Student Rankings Table - only students with transactions
    $tableWhereSql = $hasFilter ? "WHERE " . implode(" AND ", $where) : "";
    $sqlTable = "SELECT 
                    s.student_id, s.number_in_class as number, s.first_name_th, s.last_name_th, s.room, s.grade_level,
                    SUM(CASE WHEN t.points > 0 THEN t.points ELSE 0 END) as plus_points,
                    SUM(CASE WHEN t.points < 0 THEN ABS(t.points) ELSE 0 END) as minus_points,
                    (100 + SUM(t.points)) as remaining_score,
                    COUNT(t.id) as total_transactions
                 FROM students s
                 INNER JOIN point_transactions t ON s.id = t.student_id
                 $tableWhereSql
                 GROUP BY s.id
                 ORDER BY remaining_score ASC, s.room ASC, s.number_in_class ASC";
    $stmt = $pdo->prepare($sqlTable);
    $stmt->execute($params);
    $studentData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return info about scope for frontend
    echo json_encode([
        'success' => true,
        'scope' => [
            'role' => $role,
            'room' => $room,
            'grade' => $grade
        ],
        'charts' => [
            'categories' => $topCategories,
            'negative' => $topNegative,
            'positive' => $topPositive
        ],
        'students' => $studentData
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
