<?php
require_once 'config/env_loader.php';
loadEnv(__DIR__ . '/.env');

require_once 'config/db.php';

try {
    echo "Attempting connection to: " . ($_ENV['DB_HOST'] ?? 'not set') . " with user: " . ($_ENV['DB_USER'] ?? 'not set') . "\n";
    // Attempt a simple query to verify connection
    $stmt = $pdo->query("SELECT 1");
    if ($stmt) {
        echo "Database connection successful!\n";
    } else {
        echo "Database connection failed (query returned false).\n";
    }
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
}
