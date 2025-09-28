<?php
header('Content-Type: application/json');
echo json_encode([
    'message' => 'PHP is working',
    'php_version' => PHP_VERSION,
    'timestamp' => date('Y-m-d H:i:s'),
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'
]);
?>
