#!/usr/bin/env php
<?php

// Get all CSS files
$cssFiles = glob('assets/css/*.css');
$cssFiles = array_filter($cssFiles, function($file) {
    return !strpos($file, 'global.css'); // Exclude global.css itself
});

echo "Analyzing CSS files for duplicate styles...\n\n";

$duplicatePatterns = [];

foreach ($cssFiles as $file) {
    $content = file_get_contents($file);
    echo "Analyzing: $file\n";
    
    // Check for CSS variables
    if (preg_match('/:root\s*{[^}]+}/', $content)) {
        $duplicatePatterns['css_variables'][] = $file;
    }
    
    // Check for common body styles
    if (preg_match('/body\s*{[^}]*font-family[^}]*}/', $content)) {
        $duplicatePatterns['body_styles'][] = $file;
    }
    
    // Check for main class
    if (preg_match('/\.main\s*{[^}]*padding[^}]*}/', $content)) {
        $duplicatePatterns['main_styles'][] = $file;
    }
    
    // Check for card styles
    if (preg_match('/\.card\s*{[^}]*border-radius[^}]*}/', $content)) {
        $duplicatePatterns['card_styles'][] = $file;
    }
    
    // Check for card-header styles
    if (preg_match('/\.card-header\s*{[^}]*background-color[^}]*var\(--maroon\)[^}]*}/', $content)) {
        $duplicatePatterns['card_header_styles'][] = $file;
    }
    
    // Check for table styles
    if (preg_match('/\.table thead th\s*{[^}]*background-color[^}]*var\(--maroon\)[^}]*}/', $content)) {
        $duplicatePatterns['table_styles'][] = $file;
    }
    
    // Check for h1 styles
    if (preg_match('/h1\s*{[^}]*color[^}]*var\(--maroon\)[^}]*border-bottom[^}]*}/', $content)) {
        $duplicatePatterns['h1_styles'][] = $file;
    }
}

echo "\n=== DUPLICATE STYLE ANALYSIS ===\n";

foreach ($duplicatePatterns as $pattern => $files) {
    if (count($files) > 1) {
        echo "\n$pattern found in " . count($files) . " files:\n";
        foreach ($files as $file) {
            echo "  - $file\n";
        }
    }
}

echo "\n=== RECOMMENDED ACTIONS ===\n";
echo "1. Remove duplicate :root CSS variable declarations\n";
echo "2. Move common body styles to global.css\n";
echo "3. Move common .main, .card, .card-header styles to global.css\n";
echo "4. Move common table styles to global.css\n";
echo "5. Move common heading styles to global.css\n";

?>
