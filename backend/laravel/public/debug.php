<?php
// Debug script to test Laravel initialization step by step

header('Content-Type: application/json');

try {
    echo json_encode(['step' => 1, 'message' => 'PHP is working']) . "\n";
    
    // Check if vendor exists
    if (!file_exists(__DIR__.'/../vendor/autoload.php')) {
        throw new Exception('vendor/autoload.php not found');
    }
    echo json_encode(['step' => 2, 'message' => 'vendor/autoload.php found']) . "\n";
    
    // Try to load autoloader
    require __DIR__.'/../vendor/autoload.php';
    echo json_encode(['step' => 3, 'message' => 'Autoloader loaded']) . "\n";
    
    // Check if bootstrap exists
    if (!file_exists(__DIR__.'/../bootstrap/app.php')) {
        throw new Exception('bootstrap/app.php not found');
    }
    echo json_encode(['step' => 4, 'message' => 'bootstrap/app.php found']) . "\n";
    
    // Try to load Laravel app
    $app = require_once __DIR__.'/../bootstrap/app.php';
    echo json_encode(['step' => 5, 'message' => 'Laravel app loaded']) . "\n";
    
    echo json_encode(['step' => 6, 'message' => 'Laravel is working!', 'version' => $app->version()]) . "\n";
    
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]) . "\n";
} catch (Error $e) {
    echo json_encode(['error' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()]) . "\n";
}
?>
