<?php
$data = [
    'date_val' => '2026-04-21',
    'activity' => 'Test Event from scratch',
    'day_type' => 'ปกติ',
    'category' => 'student,teacher',
    'note' => 'Test note'
];
$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data)
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents('http://localhost/cnpapp/api/calendar.php?action=saveEvent', false, $context);
echo "Result: " . $result . PHP_EOL;
?>
