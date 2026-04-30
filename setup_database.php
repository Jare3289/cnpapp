<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'cnpapp_system';

try {
    // 1. เชื่อมต่อ MySQL โดยไม่ระบุ DB ก่อน
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. สร้าง Database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "✅ สร้างฐานข้อมูล '$db' สำเร็จ<br>";

    // 3. เลือกใช้ Database
    $pdo->exec("USE `$db`;");
 
    // 4. สร้างตาราง users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('admin', 'teacher', 'student') NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    echo "✅ สร้างตาราง 'users' สำเร็จ<br>";

    // 5. สร้างตาราง students
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(20) NOT NULL UNIQUE,
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        full_name VARCHAR(255),
        class VARCHAR(50),
        user_id INT,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    echo "✅ สร้างตาราง 'students' สำเร็จ<br>";

    // 6. สร้างตาราง teachers
    // ตรวจสอบและลบคอลัมน์ที่ซ้ำซ้อน/ไม่ได้ใช้แล้ว
    $oldCols = ['first_name', 'last_name', 'full_name', 'class_assigned', 'birth_date', 'appointment_date', 'current_location', 'full_name_th'];
    foreach ($oldCols as $col) {
        $check = $pdo->query("SHOW COLUMNS FROM teachers LIKE '$col'")->fetch();
        if ($check) {
            $pdo->exec("ALTER TABLE teachers DROP COLUMN `$col`;");
            echo "⚠️ ลบคอลัมน์ '$col' ออกจากตาราง 'teachers' แล้ว<br>";
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        teacher_id VARCHAR(50) NOT NULL UNIQUE, -- IDT
        email VARCHAR(100), -- EMAIL
        id_card VARCHAR(20), -- เลขบัตรประชาชน
        prefix VARCHAR(50), -- คำนำหน้า
        first_name_th VARCHAR(100), -- ชื่อ
        last_name_th VARCHAR(100), -- สกุล
        first_name_en VARCHAR(100), -- NAME
        last_name_en VARCHAR(100), -- SURENAME
        room VARCHAR(50), -- ห้อง
        faculty VARCHAR(100), -- คณะ
        photo VARCHAR(255), -- รูปถ่าย
        position VARCHAR(100), -- ตำแหน่ง
        academic_standing VARCHAR(100), -- วิทยฐานะ
        department VARCHAR(100), -- กลุ่มสาระการเรียนรู้
        sub_department VARCHAR(100), -- สาขาย่อย
        retirement_year INT, -- ปีที่เกษียณ
        phone VARCHAR(20), -- เบอร์โทรศัพท์
        line_id VARCHAR(100), -- ID Line
        address_no VARCHAR(50), -- บ้านเลขที่
        address_soi VARCHAR(100), -- ซอย
        address_moo VARCHAR(50), -- หมู่
        address_road VARCHAR(100), -- ถนน
        address_subdistrict VARCHAR(100), -- แขวง/ตำบล
        address_district VARCHAR(100), -- เขต/อำเภอ
        address_province VARCHAR(100), -- จังหวัด
        hometown VARCHAR(100), -- ภูมิลำเนา
        ethnicity VARCHAR(50), -- เชื้อชาติ
        nationality VARCHAR(50), -- สัญชาติ
        religion VARCHAR(50), -- ศาสนา
        signature VARCHAR(255), -- ลายเซ็น
        user_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    echo "✅ สร้าง/อัปเดตตาราง 'teachers' สำเร็จ<br>";

    // 7. สร้างตาราง classes
    $pdo->exec("CREATE TABLE IF NOT EXISTS classes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        level VARCHAR(50) NOT NULL,
        room VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");
    echo "✅ สร้างตาราง 'classes' สำเร็จ<br>";

    // 8. สร้างตาราง rooms (ลบ room และ ROOM อันเดิมออกก่อนเพื่อไม่ให้ซ้ำซ้อน)
    $pdo->exec("DROP TABLE IF EXISTS rooms;");
    $pdo->exec("DROP TABLE IF EXISTS room;");
    $pdo->exec("DROP TABLE IF EXISTS ROOM;");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS rooms (
        id INT AUTO_INCREMENT PRIMARY KEY,
        classroom_code VARCHAR(20) NOT NULL, -- เช่น 112
        class_level VARCHAR(10), -- เช่น 1
        classroom_no VARCHAR(10), -- เช่น 12
        grade_level VARCHAR(100), -- เช่น มัธยมศึกษาปีที่ 1
        location_code VARCHAR(20) NOT NULL, -- เช่น 113
        building VARCHAR(50), -- เช่น 1
        floor VARCHAR(20), -- เช่น 1
        room_no VARCHAR(20), -- เช่น 3 (ลำดับห้องในอาคาร)
        teacher_id INT, -- ครูที่ปรึกษา
        house VARCHAR(100),
        program VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;");
    echo "✅ สร้างตาราง 'rooms' สำเร็จ<br>";

    // 9. สร้างตาราง departments (กลุ่มสาระการเรียนรู้)
    $pdo->exec("CREATE TABLE IF NOT EXISTS departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name_th VARCHAR(200) NOT NULL,
        name_en VARCHAR(200) DEFAULT '',
        abbr_th VARCHAR(50)  DEFAULT '',
        abbr_en VARCHAR(50)  DEFAULT '',
        head_name VARCHAR(200) DEFAULT '',
        location VARCHAR(50) DEFAULT '',
        color VARCHAR(7) DEFAULT '#1e3c72',
        icon VARCHAR(50) DEFAULT 'fas fa-book',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "✅ สร้างตาราง 'departments' สำเร็จ<br>";

    // 9.1 สร้างตาราง sub_departments (สาขาย่อยของกลุ่มสาระ)
    $pdo->exec("CREATE TABLE IF NOT EXISTS sub_departments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        department_id INT NOT NULL,
        name_th VARCHAR(200) NOT NULL DEFAULT '',
        name_en VARCHAR(200) DEFAULT '',
        abbr_th VARCHAR(50)  DEFAULT '',
        abbr_en VARCHAR(50)  DEFAULT '',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "✅ สร้างตาราง 'sub_departments' สำเร็จ<br>";

    // 9.2 สร้างตาราง system_settings (ตั้งค่าระบบและข้อมูลโรงเรียน)
    $pdo->exec("CREATE TABLE IF NOT EXISTS system_settings (
        setting_key VARCHAR(50) PRIMARY KEY,
        setting_value TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    
    // Insert default values if table is empty or ignoring if exists
    $pdo->exec("INSERT IGNORE INTO system_settings (setting_key, setting_value) VALUES 
        ('school_code', '1018090213'),
        ('school_name', 'โรงเรียนชัยนาทพิทยาคม'),
        ('school_affiliation', 'สำนักงานเขตพื้นที่การศึกษามัธยมศึกษาอุทัยธานี ชัยนาท'),
        ('school_address', 'เลขที่ 55/30 ถนนลูกเสือ 1 ตำบลบ้านกล้วย อำเภอเมืองชัยนาท จังหวัดชัยนาท 17000'),
        ('school_phone', '056-411645'),
        ('school_email', 'natchanan@chainatpit.ac.th'),
        ('school_website', ''),
        ('current_academic_year', '2569'),
        ('current_semester', '1'),
        ('active_schedule', 'normal')
    ;");
    echo "✅ สร้างตาราง 'system_settings' สำเร็จ<br>";

    // 9.3 สร้างตาราง grade_levels (ระดับชั้น)
    $pdo->exec("CREATE TABLE IF NOT EXISTS grade_levels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        grade_name VARCHAR(100) NOT NULL UNIQUE,
        head_teacher_id INT,
        room_count INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (head_teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Insert default grade levels if empty
    $checkGrades = $pdo->query("SELECT COUNT(*) FROM grade_levels")->fetchColumn();
    if ($checkGrades == 0) {
        $grades = ['มัธยมศึกษาปีที่ 1', 'มัธยมศึกษาปีที่ 2', 'มัธยมศึกษาปีที่ 3', 'มัธยมศึกษาปีที่ 4', 'มัธยมศึกษาปีที่ 5', 'มัธยมศึกษาปีที่ 6'];
        $stmt = $pdo->prepare("INSERT INTO grade_levels (grade_name) VALUES (?)");
        foreach ($grades as $g) {
            $stmt->execute([$g]);
        }
    }
    echo "✅ สร้างตาราง 'grade_levels' สำเร็จ<br>";

    // 10. สร้างตาราง attendance
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        date DATE NOT NULL,
        status ENUM('มา', 'ขาด', 'ลา', 'สาย') NOT NULL,
        class_name VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");
    echo "✅ สร้างตาราง 'attendance' สำเร็จ<br>";

    // 10. สร้าง User Admin เริ่มต้น (ถ้ายังไม่มี)
    $checkAdmin = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $checkAdmin->execute();
    if (!$checkAdmin->fetch()) {
        $passHash = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->prepare("INSERT INTO users (username, password, role) VALUES ('admin', ?, 'admin')")
            ->execute([$passHash]);
        echo "🚀 <b>สร้าง Username สำหรับทดสอบแล้ว:</b><br>";
        echo "- <b>Username:</b> admin<br>";
        echo "- <b>Password:</b> admin123<br>";
    }

    // 11. จับคู่ครูที่ปรึกษากับห้องเรียนจากข้อมูลเดิมในตาราง teachers
    $stmt = $pdo->query("SELECT id, room FROM teachers WHERE room IS NOT NULL AND room != ''");
    $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $updateStmt = $pdo->prepare("UPDATE rooms SET teacher_id = ? WHERE classroom_code = ?");
    $matchCount = 0;
    foreach ($teachers as $t) {
        $cleanRoom = preg_replace('/[^0-9]/', '', $t['room']);
        if (strlen($cleanRoom) == 3) {
            $updateStmt->execute([$t['id'], $cleanRoom]);
            if ($updateStmt->rowCount() > 0) $matchCount++;
        }
    }
    echo "✅ จับคู่ครูที่ปรึกษากับห้องเรียนสำเร็จ ($matchCount ห้อง)<br>";

    echo "<br>🎉 <b>ตั้งค่าเสร็จสิ้น!</b> คุณสามารถเข้าหน้าเว็บและ Login ได้เลยครับ";

} catch (PDOException $e) {
    die("❌ เกิดข้อผิดพลาด: " . $e->getMessage());
}
?>
