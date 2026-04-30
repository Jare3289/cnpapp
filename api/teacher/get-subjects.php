<?php
// api/teacher/get-subjects.php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];

try {
    // 1. Get subjects from PERMANENT subjects table assigned to this teacher
    $stmt = $pdo->prepare("SELECT DISTINCT subject_code FROM subjects WHERE teacher_id = ?");
    $stmt->execute([$user_id]);
    $assigned_subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 2. Get subjects recorded by this specific teacher in attendance table (historical)
    $stmt = $pdo->prepare("SELECT DISTINCT subject_code FROM attendance WHERE recorded_by = ? AND subject_code IS NOT NULL AND subject_code != ''");
    $stmt->execute([$user_id]);
    $recorded_subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Combine and unique for teacher's own subjects
    $my_subjects = array_unique(array_merge($assigned_subjects, $recorded_subjects));
    sort($my_subjects);

    // 3. Get ALL subjects from PERMANENT table
    $stmt = $pdo->query("SELECT DISTINCT subject_code FROM subjects");
    $perm_all = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 4. Get ALL subjects recorded in the system (historical)
    $stmt = $pdo->query("SELECT DISTINCT subject_code FROM attendance WHERE subject_code IS NOT NULL AND subject_code != ''");
    $hist_all = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Combine and unique for all subjects
    $all_subjects = array_unique(array_merge($perm_all, $hist_all));
    sort($all_subjects);

    echo json_encode([
        'success' => true, 
        'my_subjects' => $my_subjects,
        'all_subjects' => $all_subjects
    ]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
