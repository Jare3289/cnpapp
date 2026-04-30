<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Hide raw HTML errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    echo json_encode(['error' => "PHP Error: $errstr in $errfile on line $errline"]);
    exit;
});

require_once '../../config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

set_time_limit(300);
ignore_user_abort(true);

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

// Mapping Thai Headers to Database Columns
$mapping = [
    "IDS (รหัสนักเรียน)" => "student_id",
    "เลขที่" => "number_in_class",
    "ห้อง" => "room",
    "ระดับชั้น" => "grade_level",
    "คณะ" => "faculty",
    "รูปถ่าย" => "photo",
    "คำนำหน้าชื่อ" => "prefix",
    "ชื่อ" => "first_name_th",
    "นามสกุล" => "last_name_th",
    "ชื่อจริง" => "full_name_th",
    "NAME" => "first_name_en",
    "SURENAME" => "last_name_en",
    "ชื่อเล่น" => "nickname",
    "อีเมล" => "email",
    "เพศ" => "gender",
    "เพศกำเนิด" => "birth_sex",
    "เลขบัตรประชาชน" => "id_card",
    "เชื้อชาติ" => "ethnicity",
    "สัญชาติ" => "nationality",
    "ศาสนา" => "religion",
    "วันเดือนปีเกิด" => "birth_date",
    "เป็นบุตรคนที่" => "child_order",
    "เบอร์โทรศัพท์" => "phone",
    "IDLine" => "line_id",
    "Facebook" => "facebook",
    "ที่อยู่ปัจจุบันเป็น" => "address_status",
    "บ้านเลขที่" => "reg_house_no",
    "ซอย" => "reg_soi",
    "ถนน" => "reg_road",
    "หมู่" => "reg_moo",
    "ชื่อหมู่บ้าน" => "reg_village",
    "แขวง/ตำบล" => "reg_subdistrict",
    "เขต/อำเภอ" => "reg_district",
    "จังหวัด" => "reg_province",
    "รหัสไปรษณีย์" => "reg_zipcode",
    "บ้านเลขที่ปัจจุบัน" => "curr_house_no",
    "ซอยปัจจุบัน" => "curr_soi",
    "ถนนปัจจุบัน" => "curr_road",
    "หมู่ปัจจุบัน" => "curr_moo",
    "ชื่อหมู่บ้านปัจจุบัน" => "curr_village",
    "แขวง/ตำบลปัจจุบัน" => "curr_subdistrict",
    "เขต/อำเภอปัจจุบัน" => "curr_district",
    "จังหวัดปัจจุบัน" => "curr_province",
    "รหัสไปรษณีย์ปัจจุบัน" => "curr_zipcode",
    "พิกัดที่อยู่ปัจจุบัน" => "location_coords",
    "จุดสังเกต" => "location_landmark",
    "ผู้ใหญ่บ้าน" => "village_headman",
    "กำนัน" => "subdistrict_headman",
    "ประเภทบ้าน" => "house_type",
    "ลักษณะบ้านที่อยู่" => "house_style",
    "สภาพตัวบ้าน" => "house_condition",
    "ความสะอาด" => "house_cleanliness",
    "ไฟฟ้า" => "has_electricity",
    "น้ำ" => "has_water",
    "ห้องน้ำ" => "has_toilet",
    "ระยะห่างจากโรงเรียน" => "dist_to_school",
    "ใช้เวลาเดินทาง" => "travel_time",
    "เดินทางโดย" => "travel_method",
    "คำนำหน้าบิดา" => "f_prefix",
    "ชื่อบิดา" => "f_first_name",
    "สกุลบิดา" => "f_last_name",
    "อายุบิดา" => "f_age",
    "เบอร์โทรศัพท์บิดา" => "f_phone",
    "วุฒิการศึกษาบิดา" => "f_education",
    "อาชีพบิดา" => "f_job",
    "สถานที่ทำงานบิดา" => "f_workplace",
    "สถานะทางครอบครัวบิดา" => "f_family_status",
    "เบิกค่าเล่าเรียนบิดา" => "f_welfare",
    "รายได้ต่อเดือนบิดา" => "f_income",
    "คำนำหน้ามารดา" => "m_prefix",
    "ชื่อมารดา" => "m_first_name",
    "สกุลมารดา" => "m_last_name",
    "อายุมารดา" => "m_age",
    "เบอร์โทรศัพท์มารดา" => "m_phone",
    "วุฒิการศึกษามารดา" => "m_education",
    "อาชีพมารดา" => "m_job",
    "สถานที่ทำงานมารดา" => "m_workplace",
    "สถานะทางครอบครัวมารดา" => "m_family_status",
    "เบิกค่าเล่าเรียนมารดา" => "m_welfare",
    "รายได้ต่อเดือนมารดา" => "m_income",
    "สถานภาพครอบครัว" => "family_status",
    "ความสัมพันธ์" => "guardian_relation",
    "คำนำหน้าผู้ปกครอง" => "g_prefix",
    "ชื่อผู้ปกครอง" => "g_first_name",
    "สกุลผู้ปกครอง" => "g_last_name",
    "อายุผู้ปครอง" => "g_age",
    "เบอร์โทรศัพท์ผู้ปกครอง" => "g_phone",
    "วุฒิการศึกษาผู้ปกครอง" => "g_education",
    "อาชีพผู้ปกครอง" => "g_job",
    "สถานที่ทำงานผู้ปกครอง" => "g_workplace",
    "รายได้ต่อเดือนผู้ปกครอง" => "g_income",
    "สมาชิกครอบครัวทั้งหมด" => "total_family_members",
    "เป็นชาย" => "male_members",
    "เป็นหญิง" => "female_members",
    "พี่น้องร่วมบิดามารดา" => "full_siblings",
    "พี่น้องร่วมบิดามารดาเป็นชาย" => "full_siblings_male",
    "พี่น้องร่วมบิดามารดาเป็นหญิง" => "full_siblings_female",
    "พี่น้องต่างบิดามารดา" => "half_siblings",
    "พี่น้องต่างบิดามารดาเป็นชาย" => "half_siblings_male",
    "พี่น้องต่างบิดามารดาเป็นหญิง" => "half_siblings_female",
    "ความสัมพันธ์ของสมาชิก" => "family_relationship",
    "ความสัมพันธ์กับบิดา" => "rel_father",
    "ความสัมพันธ์กับมารดา" => "rel_mother",
    "ความสัมพันธ์กับพี่ชายน้องชาย" => "rel_brothers",
    "ความสัมพันธ์กับพี่สาวนน้องสาว" => "rel_sisters",
    "ความสัมพันธ์ปู่ย่าตายาย" => "rel_grandparents",
    "ความสัมพันธ์กับญาติ" => "rel_relatives",
    "เวลาอยู่ร่วมกัน" => "time_spent_together",
    "ได้เงินจาก" => "allowance_source",
    "ได้เงินวันละ" => "allowance_per_day",
    "ภาระรับผิดชอบ" => "responsibilities",
    "นักเรียนอยู่กับใครเมื่อผู้ปกครองไม่อยู่" => "caregiver_when_away",
    "ทำงานพิเศษ" => "part_time_job",
    "รายได้" => "part_time_income",
    "น้ำหนัก" => "weight",
    "ส่วนสูง" => "height",
    "กรุ๊ปเลือด" => "blood_group",
    "แพ้อาหาร" => "food_allergies",
    "แพ้ยา" => "drug_allergies",
    "โรคประจำตัว" => "congenital_disease",
    "ฉีดวัคซีนโควิด" => "covid_vaccine",
    "เข้าถึงอินเทอร์เน็ต" => "internet_access",
    "ใช้โซเชียลมีเดีย" => "social_media_usage",
    "ความสามารถพิเศษ" => "talents",
    "ความสนใจ" => "interests",
    "งานอดิเรก" => "hobbies"
];

try {
    $handle = fopen($file['tmp_name'], "r");
    
    // Check and remove BOM
    $bom = fread($handle, 3);
    if ($bom != "\xEF\xBB\xBF") {
        rewind($handle);
    }

    // Detect delimiter
    $firstLine = fgets($handle);
    $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
    rewind($handle);
    if ($bom == "\xEF\xBB\xBF") fread($handle, 3);

    // Read headers
    $headersRaw = fgetcsv($handle, 0, $delimiter);
    $headersRaw = array_map(function($h) { return trim($h, " \t\n\r\0\x0B\""); }, $headersRaw);

    $successCount = 0;
    $pdo->beginTransaction();

    // Diagnostic: Check if columns exist
    $query = $pdo->query("DESCRIBE students");
    $dbCols = $query->fetchAll(PDO::FETCH_COLUMN);
    // Log for debugging (will show in response if error occurs later)
    $availableCols = implode(', ', $dbCols);

    $stmtUser = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'student') 
                                ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)");
    $stmtGetUid = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    
    while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
        if (empty($row) || (count($row) === 1 && $row[0] === null)) continue;
        
        // Pad row to match headers
        if (count($row) < count($headersRaw)) $row = array_pad($row, count($headersRaw), '');
        $rawData = array_combine($headersRaw, $row);

        // Required Check
        $stdId = trim($rawData['IDS (รหัสนักเรียน)'] ?? $rawData['รหัสนักเรียน'] ?? '');
        $firstNameTh = trim($rawData['ชื่อ'] ?? '');
        
        if (!$stdId || !$firstNameTh) continue;

        // 1. Create User
        $stmtUser->execute([$stdId, password_hash('cnp12345', PASSWORD_DEFAULT)]);
        $uid = $pdo->lastInsertId();
        if (!$uid) {
            $stmtGetUid->execute([$stdId]);
            $uid = $stmtGetUid->fetchColumn();
        }

        // 2. Prepare Data for Students table
        $fields = ['user_id'];
        $placeholders = ['?'];
        $values = [$uid];

        foreach ($mapping as $thaiHeader => $dbCol) {
            if (isset($rawData[$thaiHeader])) {
                $fields[] = $dbCol;
                $placeholders[] = '?';
                
                $val = trim($rawData[$thaiHeader]);
                // Handle date conversion if needed
                if ($dbCol === 'birth_date' && !empty($val)) {
                    // ปรับตัวคั่นให้เป็น - เพื่อให้จัดการง่าย
                    $val = str_replace(['/', '.'], '-', $val);
                    $dateParts = explode('-', $val);
                    
                    if (count($dateParts) === 3) {
                        // พยายามตรวจจับรูปแบบ: [Y, M, D] หรือ [D, M, Y]
                        // ปกติไฟล์ไทยมักเป็น DD-MM-YYYY
                        if (strlen($dateParts[2]) === 4) { // ถ้าตัวหลังสุดเป็นปี
                            $d = (int)$dateParts[0]; $m = (int)$dateParts[1]; $y = (int)$dateParts[2];
                        } else { // สมมติว่าเป็น YYYY-MM-DD
                            $y = (int)$dateParts[0]; $m = (int)$dateParts[1]; $d = (int)$dateParts[2];
                        }
                        
                        // ถ้าปีเกิน 2400 แสดงว่าเป็น พ.ศ. ให้ลบออก 543
                        if ($y > 2400) $y -= 543;
                        
                        // ตรวจเช็คความถูกต้องของวันที่ (Basic check)
                        if ($y > 1900 && $m >= 1 && $m <= 12 && $d >= 1 && $d <= 31) {
                            $val = sprintf('%04d-%02d-%02d', $y, $m, $d);
                        } else {
                            $val = date('Y-m-d', strtotime($val));
                        }
                    } else {
                        $val = date('Y-m-d', strtotime($val));
                    }
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

                $values[] = $val;
            }
        }

        $sql = "REPLACE INTO students (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
        $stmtStd = $pdo->prepare($sql);
        $stmtStd->execute($values);
        
        $successCount++;
    }

    $pdo->commit();
    fclose($handle);
    echo json_encode(['success' => true, 'message' => "นำเข้าข้อมูลนักเรียนสำเร็จ $successCount รายการ"]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    $debugInfo = isset($availableCols) ? "\nColumns in DB: " . $availableCols : "";
    echo json_encode(['error' => 'เกิดข้อผิดพลาด: ' . $e->getMessage() . $debugInfo]);
}
