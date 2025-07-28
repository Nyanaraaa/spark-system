<?php
// Script to add global.css to pages that don't have it
$files_to_update = [
    'modules/supervisor/pages/leaderboard_history.php',
    'modules/supervisor/pages/leaderboard.php',
    'modules/supervisor/pages/usage_history.php',
    'modules/supervisor/pages/staff_record.php',
    'modules/supervisor/pages/staff_list.php',
    'modules/supervisor/pages/manage_supplies.php',
    'modules/supervisor/pages/request.php',
    'modules/supervisor/pages/manage_schedule.php',
    'modules/staff/pages/usage_history.php',
    'modules/staff/pages/request.php',
    'modules/staff/pages/progress_assessment.php',
    'modules/staff/pages/progress_report.php',
    'modules/staff/pages/leaderboard_history.php',
    'modules/staff/pages/leaderboard.php',
    'modules/staff/pages/profile.php'
];

foreach ($files_to_update as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Check if global.css is already included
        if (strpos($content, 'global.css') === false) {
            // Find the manifest.json line and add global.css after it
            $manifest_pattern = '/(<link rel="manifest"[^>]*>)/';
            $replacement = '$1' . "\n" . '    <link rel="stylesheet" href="../../../assets/css/global.css?v=<?php echo filemtime(\'../../../assets/css/global.css\'); ?>">';
            
            $updated_content = preg_replace($manifest_pattern, $replacement, $content);
            
            if ($updated_content !== $content) {
                file_put_contents($file, $updated_content);
                echo "Updated: $file\n";
            } else {
                echo "Could not update: $file (pattern not found)\n";
            }
        } else {
            echo "Already has global.css: $file\n";
        }
    } else {
        echo "File not found: $file\n";
    }
}

echo "Done!\n";
?>
