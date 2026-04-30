<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

// 1. Authentication — รองรับทั้ง session format เก่า และใหม่
if (isset($_SESSION['user'])) {
    // New format (set by updated login.php)
    $currentUser = $_SESSION['user'];
} elseif (isset($_SESSION['user_id'])) {
    // Legacy format (users logged in before the fix)
    $currentUser = [
        'id'       => $_SESSION['user_id'],
        'username' => $_SESSION['username'] ?? '',
        'role'     => $_SESSION['role'] ?? '',
    ];
} else {
    http_response_code(401);
    echo json_encode(['error' => 'กรุณาเข้าสู่ระบบ']);
    exit;
}

$role = $currentUser['role'];

// 2. Detect input type: FormData ($_POST) or raw JSON
$isFormData = !empty($_POST);
$input = $isFormData ? $_POST : json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$id = $input['id'];

// 3. Permission Check
if ($role !== 'admin' && $role !== 'teacher') {
    $stmt = $pdo->prepare("SELECT user_id FROM students WHERE id = ?");
    $stmt->execute([$id]);
    $rec = $stmt->fetch();
    if (!$rec || $rec['user_id'] != $currentUser['id']) {
        http_response_code(403);
        echo json_encode(['error' => 'คุณไม่มีสิทธิ์แก้ไขข้อมูลนี้']);
        exit;
    }
}

// 4. Handle photo upload
$fieldsToUpdate = [];
$values = [];

if (!empty($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
    $uploadDir = __DIR__ . '/../../public/uploads/students/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (in_array($ext, $allowed) && $_FILES['photo']['size'] <= 2 * 1024 * 1024) {
        $filename = 'student_' . $id . '_' . time() . '.' . $ext;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
            $fieldsToUpdate[] = "`photo` = ?";
            $values[] = 'public/uploads/students/' . $filename;
        }
    }
}

// 5. Build dynamic UPDATE query
$allowedFields = [
    'student_id', 'prefix', 'first_name_th', 'last_name_th', 'first_name_en', 'last_name_en',
    'nickname', 'id_card', 'birth_date', 'nationality', 'ethnicity', 'religion',
    'grade_level', 'room', 'number_in_class', 'faculty', 'phone', 'email', 'line_id', 'facebook',
    'address_status',
    'curr_house_no', 'curr_moo', 'curr_soi', 'curr_road', 'curr_subdistrict', 'curr_district', 'curr_province', 'curr_zipcode',
    'reg_house_no', 'reg_moo', 'reg_soi', 'reg_road', 'reg_subdistrict', 'reg_district', 'reg_province', 'reg_zipcode',
    'f_prefix', 'f_first_name', 'f_last_name', 'f_age', 'f_phone', 'f_job', 'f_income',
    'm_prefix', 'm_first_name', 'm_last_name', 'm_age', 'm_phone', 'm_job', 'm_income',
    'g_prefix', 'g_first_name', 'g_last_name', 'g_age', 'g_phone', 'g_job', 'g_income', 'guardian_relation',
    'weight', 'height', 'blood_group', 'food_allergies', 'drug_allergies', 'congenital_disease'
];

foreach ($input as $key => $val) {
    if (in_array($key, $allowedFields)) {
        $fieldsToUpdate[] = "`$key` = ?";
        $values[] = ($val === '') ? null : $val;
    }
}

if (empty($fieldsToUpdate)) {
    echo json_encode(['success' => true, 'message' => 'ไม่มีข้อมูลที่ต้องแก้ไข']);
    exit;
}

$values[] = $id;
$sql = "UPDATE students SET " . implode(', ', $fieldsToUpdate) . " WHERE id = ?";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);
    echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลสำเร็จ']);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
