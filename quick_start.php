<?php

declare(strict_types=1);

/**
 * Quick Start Script for URL Shortener Library
 * 
 * This script demonstrates how to quickly get started with the library
 * Run: php quick_start.php
 */

// Check if autoload exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "⚠️  Composer dependencies not installed!\n";
    echo "Please run: composer install\n\n";
    
    echo "If you don't have Composer installed, download it from: https://getcomposer.org/\n";
    echo "Then run:\n";
    echo "1. composer install\n";
    echo "2. php quick_start.php\n\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

use UrlShortener\UrlShortener;
use UrlShortener\Storage\MemoryStorage;
use UrlShortener\Generator\RandomCodeGenerator;

echo "🚀 URL Shortener Library - Quick Start\n";
echo "=====================================\n\n";

// Create the shortener
$storage = new MemoryStorage();
$generator = new RandomCodeGenerator();
$shortener = new UrlShortener($storage, $generator);

// Test URL
$longUrl = 'https://www.example.com/very/long/url/that/should/be/shortened';

echo "📝 Original URL: {$longUrl}\n\n";

// Shorten the URL
$shortCode = $shortener->shorten($longUrl);
echo "✂️  Short code: {$shortCode}\n";

// Expand it back
$expandedUrl = $shortener->expand($shortCode);
echo "🔗 Expanded URL: {$expandedUrl}\n\n";

// Test multiple clicks for analytics
echo "📊 Simulating clicks for analytics...\n";
for ($i = 1; $i <= 3; $i++) {
    $shortener->expand($shortCode);
    echo "   Click #{$i}\n";
}

// Get analytics
$analytics = $shortener->getAnalytics($shortCode);
echo "\n📈 Analytics:\n";
echo "   Total clicks: {$analytics['total_clicks']}\n";
echo "   Created: " . $analytics['created_at']->format('Y-m-d H:i:s') . "\n";
echo "   Click records: " . count($analytics['clicks']) . "\n\n";

echo "✅ Success! The URL Shortener library is working correctly.\n\n";

echo "🎯 Next steps:\n";
echo "   • Check out examples/basic_usage.php for more examples\n";
echo "   • Read the README.md for detailed documentation\n";
echo "   • Run tests with: composer test\n";
echo "   • Check code quality with: composer analyse\n\n";

echo "🔧 Available storage backends:\n";
echo "   • MemoryStorage (for testing)\n";
echo "   • FileStorage (for file-based persistence)\n";
echo "   • DatabaseStorage (for MySQL/PostgreSQL)\n\n";

echo "Happy URL shortening! 🎉\n";
