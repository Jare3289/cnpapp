<?php
require_once 'config.php';

function convertRoomFormat($roomStr) {
    if (!$roomStr) return $roomStr;
    
    // Check if it's already in 3-digit format (like 101, 312, 605)
    if (preg_match('/^[1-6]\d{2}$/', trim($roomStr))) {
        return trim($roomStr);
    }
    
    // Extract all numbers from the string
    preg_match_all('/\d+/', $roomStr, $matches);
    
    if (isset($matches[0]) && count($matches[0]) >= 2) {
        $grade = $matches[0][0]; // First number
        $room = $matches[0][1]; // Second number
        
        // Grade should be 1-6
        if ($grade >= 1 && $grade <= 6) {
            return $grade . str_pad($room, 2, '0', STR_PAD_LEFT);
        }
    }
    
    return trim($roomStr); // Return original if couldn't parse
}

$tables = ['teachers', 'students'];
$results = [];

foreach ($tables as $table) {
    try {
        $stmt = $pdo->query("SELECT id, room FROM $table WHERE room IS NOT NULL AND room != ''");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $results[$table] = [];
        foreach ($rows as $row) {
            $oldRoom = $row['room'];
            $newRoom = convertRoomFormat($oldRoom);
            if ($oldRoom !== $newRoom) {
                $results[$table][] = [
                    'id' => $row['id'],
                    'old' => $oldRoom,
                    'new' => $newRoom
                ];
            }
        }
    } catch (PDOException $e) {
        // Table might not exist or no room column
    }
}

file_put_contents('scratch/room_conversion_preview.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
echo "Preview generated.";
