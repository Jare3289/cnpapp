<?php
header('Content-Type: application/json');
require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

set_time_limit(300);

if (!isset($_FILES['excelFile'])) {
    echo json_encode(['error' => 'ไม่พบไฟล์ที่อัปโหลด']);
    exit;
}

$file = $_FILES['excelFile'];
$ext = pathinfo($file['name'], PATHINFO_EXTENSION);

if (strtolower($ext) !== 'csv') {
    echo json_encode(['error' => 'กรุณาใช้ไฟล์นามสกุล .csv']);
    exit;
}

try {
    // 1. ตรวจสอบและเพิ่ม Columns ที่อาจจะยังไม่มีในตาราง teachers
    $fields = [
        'email' => 'VARCHAR(100)',
        'id_card' => 'VARCHAR(20)',
        'prefix' => 'VARCHAR(50)',
        'first_name_th' => 'VARCHAR(100)',
        'last_name_th' => 'VARCHAR(100)',
        'first_name_en' => 'VARCHAR(100)',
        'last_name_en' => 'VARCHAR(100)',
        'room' => 'VARCHAR(50)',
        'faculty' => 'VARCHAR(100)',
        'photo' => 'VARCHAR(255)',
        'position' => 'VARCHAR(100)',
        'academic_standing' => 'VARCHAR(100)',
        'department' => 'VARCHAR(100)',
        'sub_department' => 'VARCHAR(100)',
        'retirement_year' => 'INT',
        'phone' => 'VARCHAR(20)',
        'line_id' => 'VARCHAR(100)',
        'address_no' => 'VARCHAR(50)',
        'address_soi' => 'VARCHAR(100)',
        'address_moo' => 'VARCHAR(50)',
        'address_road' => 'VARCHAR(100)',
        'address_subdistrict' => 'VARCHAR(100)',
        'address_district' => 'VARCHAR(100)',
        'address_province' => 'VARCHAR(100)',
        'hometown' => 'VARCHAR(100)',
        'ethnicity' => 'VARCHAR(50)',
        'nationality' => 'VARCHAR(50)',
        'religion' => 'VARCHAR(50)',
        'signature' => 'VARCHAR(255)'
    ];

    foreach ($fields as $col => $type) {
        $check = $pdo->query("SHOW COLUMNS FROM teachers LIKE '$col'")->fetch();
        if (!$check) {
            $pdo->exec("ALTER TABLE teachers ADD COLUMN $col $type NULL");
        }
    }

    $handle = fopen($file['tmp_name'], "r");
    // Check and remove BOM
    $bom = fread($handle, 3);
    if ($bom != "\xEF\xBB\xBF") rewind($handle);

    $firstLine = fgets($handle);
    $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
    rewind($handle);
    if ($bom == "\xEF\xBB\xBF") fread($handle, 3);

    $headers = fgetcsv($handle, 0, $delimiter);
    $headers = array_map(function($h) { return trim($h, " \t\n\r\0\x0B\""); }, $headers);

    $successCount = 0;
    $pdo->beginTransaction();

    $stmtUser = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'teacher') 
                                ON DUPLICATE KEY UPDATE password = VALUES(password)");
    $stmtGetUid = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    
    // Mapping CSV headers to SQL columns
    $mapping = [
        'IDT' => 'teacher_id',
        'EMAIL' => 'email',
        'เลขบัตรประชาชน' => 'id_card',
        'คำนำหน้า' => 'prefix',
        'ชื่อ' => 'first_name_th',
        'สกุล' => 'last_name_th',
        'NAME' => 'first_name_en',
        'SURENAME' => 'last_name_en',
        'ห้อง' => 'room',
        'คณะ' => 'faculty',
        'รูปถ่าย' => 'photo',
        'ตำแหน่ง' => 'position',
        'วิทยฐานะ' => 'academic_standing',
        'กลุ่มสาระการเรียนรู้' => 'department',
        'สาขาย่อย' => 'sub_department',
        'ปีที่เกษียณ' => 'retirement_year',
        'เบอร์โทรศัพท์' => 'phone',
        'ID Line' => 'line_id',
        'บ้านเลขที่' => 'address_no',
        'ซอย' => 'address_soi',
        'หมู่' => 'address_moo',
        'ถนน' => 'address_road',
        'แขวง/ตำบล' => 'address_subdistrict',
        'เขต/อำเภอ' => 'address_district',
        'จังหวัด' => 'address_province',
        'ภูมิลำเนา' => 'hometown',
        'เชื้อชาติ' => 'ethnicity',
        'สัญชาติ' => 'nationality',
        'ศาสนา' => 'religion',
        'ลายเซ็น' => 'signature'
    ];

    while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
        set_time_limit(30);
        if (empty($row) || (count($row) === 1 && $row[0] === null)) continue;
        
        $data = @array_combine($headers, array_pad($row, count($headers), ''));
        if (!$data) continue;

        $idt = trim($data['IDT'] ?? '');
        $email = trim($data['EMAIL'] ?? '');

        // ใช้ Email เป็น Username ถ้าไม่มีให้ใช้ IDT เป็นตัวสำรอง
        $username = filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : $idt;
        if (!$username) continue; // ถ้าไม่มีทั้งคู่ให้ข้าม

        // User account
        $hashedPwd = password_hash('cnp12345', PASSWORD_DEFAULT);
        $stmtUser->execute([$username, $hashedPwd]);
        $stmtGetUid->execute([$username]);
        $uid = $stmtGetUid->fetchColumn();

        // Teacher data
        $insertCols = ['user_id'];
        $insertVals = [$uid];
        $placeholders = ['?'];

        foreach ($mapping as $csvHeader => $dbCol) {
            $val = trim($data[$csvHeader] ?? '');
            
            // ตรวจสอบและเติมเลข 0 สำหรับหมายเลขโทรศัพท์
            if ($dbCol === 'phone' && $val !== '' && $val[0] !== '0' && strlen($val) >= 9) {
                $val = '0' . $val;
            }

            // Handle room format conversion (e.g., "1/1" to "101")
            if ($dbCol === 'room' && !empty($val)) {
                if (!preg_match('/^[1-6]\d{2}$/', $val)) {
                    preg_match_all('/\d+/', $val, $matches);
                    if (isset($matches[0]) && count($matches[0]) >= 2) {
                        $grade = $matches[0][0];
                        $room = $matches[0][1];
                        if ($grade >= 1 && $grade <= 6) {
                            $val = $grade . str_pad($room, 2, '0', STR_PAD_LEFT);
                        }
                    }
                }
            }

            $insertCols[] = $dbCol;
            $insertVals[] = $val;
            $placeholders[] = '?';
        }

        $sql = "REPLACE INTO teachers (" . implode(',', $insertCols) . ") VALUES (" . implode(',', $placeholders) . ")";
        $pdo->prepare($sql)->execute($insertVals);
        
        $successCount++;
    }

    $pdo->commit();
    fclose($handle);
    echo json_encode(['success' => true, 'message' => "นำเข้าข้อมูลคุณครูสำเร็จ $successCount รายการ"]);
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()]);
}
