<?php
// api/login.php (ฉบับทดสอบ Debug)
header('Content-Type: application/json');
require_once '../config.php';
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['username']) || !isset($data['password']) || !isset($data['role'])) {
    echo json_encode(['error' => 'ข้อมูลไม่ครบถ้วน']);
    exit;
}

$username = $data['username'];
$password = $data['password'];
$role = $data['role'];

try {
    // 1. ค้นหาผู้ใช้โดยเชื่อมกับตาราง roles
    $stmt = $pdo->prepare("SELECT u.*, r.name as role_name 
                           FROM users u 
                           JOIN roles r ON u.role_id = r.id 
                           WHERE u.username = ? AND r.name = ?");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. ตรวจสอบรหัสผ่าน
    if ($user && ($password === $user['password'] || password_verify($password, $user['password']))) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role_name'];
        // Full user object — used by API middleware checks
        $_SESSION['user'] = [
            'id'       => $user['id'],
            'username' => $user['username'],
            'role'     => $user['role_name'],
        ];

        $redirect = '';
        switch ($user['role_name']) {
            case 'admin': $redirect = 'views/admin_dashboard.html'; break;
            case 'teacher': $redirect = 'views/teacher_dashboard.html'; break;
            case 'student': $redirect = 'views/student_dashboard.html'; break;
            default: $redirect = 'views/index.html'; // กรณีบทบาทอื่นๆ ที่อาจเพิ่มในอนาคต
        }

        echo json_encode(['success' => true, 'redirect' => $redirect]);
    } else {
        echo json_encode(['error' => 'รหัสชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้องสำหรับบทบาทที่เลือก']);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB Error: ' . $e->getMessage()]);
}
?>
