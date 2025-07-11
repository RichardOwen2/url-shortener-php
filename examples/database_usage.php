<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use UrlShortener\UrlShortener;
use UrlShortener\Storage\DatabaseStorage;
use UrlShortener\Generator\RandomCodeGenerator;

echo "=== URL Shortener Library - Database Example ===\n\n";

// Database configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'url_shortener',
    'username' => 'root',
    'password' => '',
];

echo "Note: This example requires a MySQL database.\n";
echo "Please ensure you have a database set up with the following configuration:\n";
echo "Host: {$config['host']}\n";
echo "Database: {$config['dbname']}\n";
echo "Username: {$config['username']}\n";
echo "Password: " . (empty($config['password']) ? '(empty)' : '(set)') . "\n\n";

try {
    // Create PDO connection
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "✓ Database connection established successfully!\n\n";

    // Create storage and shortener
    $storage = new DatabaseStorage($pdo);
    $generator = new RandomCodeGenerator(8);
    $shortener = new UrlShortener($storage, $generator);

    echo "✓ Database tables created/verified successfully!\n\n";

    // Demo URLs for testing
    $testUrls = [
        'https://www.example.com/product/123',
        'https://blog.example.com/article/how-to-use-url-shortener',
        'https://docs.example.com/api/v1/documentation',
        'https://support.example.com/ticket/456789',
    ];

    $shortCodes = [];

    echo "Shortening URLs:\n";
    echo "================\n";

    foreach ($testUrls as $index => $url) {
        $metadata = [
            'category' => 'demo',
            'batch' => 'database_example',
            'index' => $index + 1,
        ];

        $shortCode = $shortener->shorten($url, null, $metadata);
        $shortCodes[] = $shortCode;

        echo "URL #{" . ($index + 1) . "}: {$url}\n";
        echo "Short code: {$shortCode}\n";
        echo "Metadata: " . json_encode($metadata) . "\n\n";
    }

    echo "Testing URL expansion and analytics:\n";
    echo "===================================\n";

    foreach ($shortCodes as $index => $shortCode) {
        echo "Testing short code: {$shortCode}\n";
        
        // Simulate multiple clicks
        for ($i = 0; $i < rand(3, 8); $i++) {
            $expandedUrl = $shortener->expand($shortCode);
            echo "  Click #{" . ($i + 1) . "}: {$expandedUrl}\n";
            usleep(50000); // Small delay
        }

        // Get analytics
        $analytics = $shortener->getAnalytics($shortCode);
        echo "  Total clicks: {$analytics['total_clicks']}\n";
        echo "  Created: " . $analytics['created_at']->format('Y-m-d H:i:s') . "\n";
        echo "  Metadata: " . json_encode($analytics['metadata']) . "\n\n";
    }

    echo "Testing advanced features:\n";
    echo "=========================\n";

    // Test URL with expiration
    $expirableUrl = 'https://example.com/limited-time-offer';
    $expiresAt = new DateTimeImmutable('+24 hours');
    $expirableCode = $shortener->shorten($expirableUrl, $expiresAt);

    echo "Created expirable URL:\n";
    echo "  URL: {$expirableUrl}\n";
    echo "  Short code: {$expirableCode}\n";
    echo "  Expires at: " . $expiresAt->format('Y-m-d H:i:s') . "\n\n";

    // Test metadata update
    $newMetadata = ['updated' => true, 'last_modified' => date('Y-m-d H:i:s')];
    $shortener->updateMetadata($shortCodes[0], $newMetadata);
    
    $updatedRecord = $shortener->getUrlRecord($shortCodes[0]);
    echo "Updated metadata for {$shortCodes[0]}:\n";
    echo "  " . json_encode($updatedRecord->getMetadata()) . "\n\n";

    // Test deletion
    $codeToDelete = array_pop($shortCodes);
    echo "Deleting short code: {$codeToDelete}\n";
    
    if ($shortener->delete($codeToDelete)) {
        echo "✓ Successfully deleted!\n";
        
        if (!$shortener->exists($codeToDelete)) {
            echo "✓ Confirmed: Short code no longer exists\n";
        }
    }

    echo "\n=== Database example completed successfully! ===\n";

} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
    echo "\nPlease check your database configuration and ensure:\n";
    echo "1. MySQL server is running\n";
    echo "2. Database '{$config['dbname']}' exists\n";
    echo "3. User has proper permissions\n\n";
    
    echo "To create the database, run:\n";
    echo "CREATE DATABASE {$config['dbname']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
