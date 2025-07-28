<?php
// Try different paths to find the correct one
$paths = [
    '../../../includes/bootstrap.php',
    __DIR__ . '/../../../includes/bootstrap.php',
    dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php'
];

foreach ($paths as $path) {
    echo "Trying path: $path\n";
    if (file_exists($path)) {
        echo "✓ Path exists!\n";
        try {
            require_once $path;
            echo "✓ Bootstrap loaded successfully!\n";
            break;
        } catch (Exception $e) {
            echo "✗ Error loading: " . $e->getMessage() . "\n";
        }
    } else {
        echo "✗ Path does not exist\n";
    }
    echo "\n";
}
?>
