<?php
require_once 'config.php';

try {
    // Get some students to link to
    $stmt = $pdo->query("SELECT student_id FROM students LIMIT 5");
    $students = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($students)) {
        die("No students found to seed data.");
    }

    $activities = [
        ['activity_name' => 'เก็บขยะรอบอาคารเรียน', 'location' => 'บริเวณอาคาร 1', 'certifier_name' => 'นายชัยนาท รักดี'],
        ['activity_name' => 'ทำความสะอาดห้องสมุด', 'location' => 'ห้องสมุดกลาง', 'certifier_name' => 'นายนิพนธ์ ใจเย็น'],
        ['activity_name' => 'ช่วยงานวันแม่แห่งชาติ', 'location' => 'หอประชุมใหญ่', 'certifier_name' => 'นางสาวสมใจ นึกงาม'],
        ['activity_name' => 'ปลูกต้นไม้ลดโลกร้อน', 'location' => 'สวนหย่อมหน้าโรงเรียน', 'certifier_name' => 'นายสมชาย สายลม']
    ];

    $stmt = $pdo->prepare("INSERT INTO public_service_records (student_id, activity_name, location, activity_date, duration, duration_unit, certifier_name, status, academic_year, semester) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($students as $index => $sid) {
        $act = $activities[$index % count($activities)];
        $date = date('Y-m-d', strtotime('-' . ($index * 2) . ' days'));
        $stmt->execute([
            $sid,
            $act['activity_name'],
            $act['location'],
            $date,
            3.05 + $index, // Mocking hours
            'ชั่วโมง',
            $act['certifier_name'],
            $index % 2 == 0 ? 'approved' : 'pending',
            '2569',
            '1'
        ]);
    }

    echo "Seed data for Public Service Activities inserted successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
