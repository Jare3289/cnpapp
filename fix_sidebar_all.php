<?php
$dir = __DIR__ . '/views';
$files = glob($dir . '/*.html');

foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // 1. Remove local toggleSidebar function definitions
    $content = preg_replace('/function toggleSidebar\(\) \{ [^}]* \}/', '', $content);
    
    // 2. Clean up any leftover empty script tags if they only contained toggleSidebar
    $content = preg_replace('/<script>\s*<\/script>/', '', $content);
    
    // 3. Standardize the hamburger button in the header if it exists
    // Replace various versions with a standard one that triggers the global toggle
    $content = preg_replace('/<button[^>]*onclick="toggleSidebar\(\)"[^>]*>.*?<\/button>/s', '<button class="btn btn-link text-navy d-lg-none px-0" onclick="toggleSidebar()"><i class="fa-solid fa-bars-staggered fs-4"></i></button>', $content);

    file_put_contents($file, $content);
}
echo "Cleaned up local toggleSidebar and standardized hamburger buttons in all views.";
?>
