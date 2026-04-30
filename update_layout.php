<?php
$dir = __DIR__ . '/views';
$files = glob($dir . '/*.html');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // 1. Force Bootstrap flex classes and background on main-panel (app-main)
    // We want: class="app-main main-panel d-flex flex-column min-vh-100" style="background-color: #f8fafc;"
    $content = preg_replace('/<main class="([^"]*app-main[^"]*)"/', '<main class="$1 d-flex flex-column min-vh-100" style="background-color: #f8fafc;"', $content);
    
    // 2. Force py-4 and flex-grow-1 on app-content
    // We want: class="app-content page-content py-4 flex-grow-1 d-flex flex-column"
    $content = preg_replace('/<div class="([^"]*app-content page-content[^"]*)"/', '<div class="$1 py-4 flex-grow-1 d-flex flex-column"', $content);
    
    // Cleanup duplicates of the classes we just added
    $classes_to_clean = ['d-flex', 'flex-column', 'min-vh-100', 'py-4', 'flex-grow-1'];
    foreach ($classes_to_clean as $class) {
        $content = str_replace("$class $class", $class, $content);
    }
    
    // Ensure styles aren't duplicated
    $content = str_replace('style="background-color: #f8fafc;" style="background-color: #f8fafc;"', 'style="background-color: #f8fafc;"', $content);

    file_put_contents($file, $content);
}
echo "Applied standardized layout (py-4 + flex) to all views.";
?>
