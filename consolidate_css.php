<?php
/**
 * CSS Consolidation Script
 * Removes duplicate styles from all CSS files that are now in global.css
 */

// Define the CSS files to process
$cssFiles = [
    'assets/css/dashboard.css',
    'assets/css/staff_list.css',
    'assets/css/supervisorassessment.css',
    'assets/css/manage_location.css',
    'assets/css/staff.css',
    'assets/css/supervisorupdate.css',
    'assets/css/supervisor_leaderboard.css',
    'assets/css/staff_leaderboard.css',
    'assets/css/supervisor_profile.css',
    'assets/css/staff_profile.css',
    'assets/css/manage_schedule.css',
    'assets/css/staff_history.css',
    'assets/css/progress_assessment.css',
    'assets/css/progress_report.css',
    'assets/css/supervisor_request.css',
    'assets/css/staff_request.css'
];

// Define patterns to remove (these are now in global.css)
$patternsToRemove = [
    // CSS Variables block
    '/:root\s*\{[^}]*\}/',
    
    // Body styles that match global patterns
    '/body\s*\{[^}]*font-family:[^}]*background-color:[^}]*color:[^}]*\}/',
    
    // Main layout
    '/\.main\s*\{[^}]*padding:[^}]*transition:[^}]*\}/',
    
    // Common typography
    '/h1\s*\{[^}]*color:\s*var\(--maroon\)[^}]*\}/',
    '/h5\s*\{[^}]*color:\s*var\(--maroon\)[^}]*\}/',
    '/\.welcome-header\s*\{[^}]*\}/',
    
    // Card styles
    '/\.card\s*\{[^}]*border-radius:\s*10px[^}]*box-shadow:[^}]*\}/',
    '/\.dashboard-card\s*\{[^}]*border-radius:\s*10px[^}]*box-shadow:[^}]*\}/',
    '/\.card-header\s*\{[^}]*background-color:\s*var\(--maroon\)[^}]*\}/',
    
    // Table styles that match global patterns
    '/\.table thead th\s*\{[^}]*background-color:\s*var\(--maroon\)[^}]*\}/',
    
    // Button styles that match global patterns
    '/\.btn-primary\s*\{[^}]*background-color:\s*var\(--maroon\)[^}]*\}/',
];

echo "Starting CSS consolidation...\n";

foreach ($cssFiles as $file) {
    if (!file_exists($file)) {
        echo "Skipping $file (not found)\n";
        continue;
    }
    
    $content = file_get_contents($file);
    $originalContent = $content;
    
    // Remove duplicate patterns
    foreach ($patternsToRemove as $pattern) {
        $content = preg_replace($pattern, '', $content);
    }
    
    // Clean up extra whitespace
    $content = preg_replace('/\n\s*\n\s*\n/', "\n\n", $content);
    $content = trim($content);
    
    // Only write if content changed
    if ($content !== $originalContent) {
        file_put_contents($file, $content);
        echo "Updated: $file\n";
    } else {
        echo "No changes: $file\n";
    }
}

echo "CSS consolidation complete!\n";
?>
