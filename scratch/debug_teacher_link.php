<?php
require_once 'config.php';

// Dump a few teachers from the users table and see if they have matching records in the teachers table
$stmt = $pdo->query("
    SELECT u.id AS u_id, u.username, u.role, t.id AS t_id, t.user_id AS t_user_id, t.teacher_id, t.email, t.first_name_th
    FROM users u
    LEFT JOIN teachers t ON u.id = t.user_id
    WHERE u.role = 'teacher'
    LIMIT 5
");
$users_with_teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Also look at some teachers from the teachers table to see what their user_id is
$stmt2 = $pdo->query("
    SELECT id AS t_id, user_id AS t_user_id, teacher_id, email, first_name_th
    FROM teachers
    LIMIT 5
");
$teachers_raw = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$result = [
    'users_left_join_teachers' => $users_with_teachers,
    'raw_teachers' => $teachers_raw
];

file_put_contents('scratch/debug_teacher_link.json', json_encode($result, JSON_PRETTY_PRINT));
echo "Done writing debug data to scratch/debug_teacher_link.json";
