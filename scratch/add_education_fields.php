<?php
require_once 'config.php';

try {
    $cols = [
        'edu_highschool' => 'VARCHAR(255) DEFAULT NULL',
        'edu_highschool_year' => 'VARCHAR(4) DEFAULT NULL',
        'edu_university' => 'VARCHAR(255) DEFAULT NULL',
        'edu_university_degree' => 'VARCHAR(255) DEFAULT NULL',
        'edu_university_year' => 'VARCHAR(4) DEFAULT NULL'
    ];

    $existingCols = array_column(
        $pdo->query("SHOW COLUMNS FROM teachers")->fetchAll(PDO::FETCH_ASSOC),
        'Field'
    );

    foreach ($cols as $col => $type) {
        if (!in_array($col, $existingCols)) {
            $pdo->exec("ALTER TABLE teachers ADD COLUMN $col $type");
            echo "Added column: $col\n";
        }
    }

    echo "Migration completed successfully.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
