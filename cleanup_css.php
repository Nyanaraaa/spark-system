<?php
/**
 * Clean up remaining CSS files
 */

$files = [
    'assets/css/staff.css',
    'assets/css/supervisorupdate.css',
    'assets/css/supervisor_leaderboard.css',
    'assets/css/staff_leaderboard.css',
    'assets/css/supervisor_profile.css',
    'assets/css/staff_profile.css',
    'assets/css/manage_schedule.css',
    'assets/css/supervisor_usage_history.css',
    'assets/css/staff_history.css',
    'assets/css/progress_assessment.css',
    'assets/css/progress_report.css'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "Skipping $file (not found)\n";
        continue;
    }
    
    $content = file_get_contents($file);
    
    // Remove :root block (CSS variables)
    $content = preg_replace('/:root\s*\{[^}]*\}/', '', $content);
    
    // Remove duplicate body styles that match the global pattern
    $content = preg_replace('/body\s*\{\s*font-family:\s*"Segoe UI"[^}]*background-color:\s*var\(--gray-light\)[^}]*\}/', '', $content);
    
    // Remove duplicate .main styles
    $content = preg_replace('/\.main\s*\{\s*padding:\s*20px[^}]*transition:[^}]*\}/', '', $content);
    
    // Remove duplicate h1 styles
    $content = preg_replace('/h1\s*\{\s*color:\s*var\(--maroon\)[^}]*text-align:\s*center[^}]*\}/', '', $content);
    
    // Remove duplicate h5 styles
    $content = preg_replace('/h5\s*\{\s*color:\s*var\(--maroon\)[^}]*margin-bottom:\s*15px[^}]*\}/', '', $content);
    
    // Remove duplicate .card styles
    $content = preg_replace('/\.card\s*\{\s*border-radius:\s*10px[^}]*box-shadow:[^}]*margin-bottom:\s*20px[^}]*\}/', '', $content);
    
    // Remove duplicate .card-header styles
    $content = preg_replace('/\.card-header\s*\{\s*background-color:\s*var\(--maroon\)[^}]*border-bottom:\s*none[^}]*\}/', '', $content);
    
    // Clean up extra whitespace
    $content = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $content);
    $content = trim($content);
    
    // Add comment at the top
    $filename = basename($file, '.css');
    $content = "/* " . ucwords(str_replace('_', ' ', $filename)) . " specific styles */\n\n" . $content;
    
    file_put_contents($file, $content);
    echo "Cleaned: $file\n";
}

echo "Cleanup complete!\n";
?>
