<?php
/**
 * Installation Verification Test
 * 
 * Run this script after installing the library to verify everything works correctly.
 * Usage: php verify_installation.php
 */

declare(strict_types=1);

// Check if autoloader exists
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "❌ Autoloader not found!\n";
    echo "Please run: composer install\n";
    exit(1);
}

require_once __DIR__ . '/vendor/autoload.php';

use UrlShortener\UrlShortener;
use UrlShortener\Storage\MemoryStorage;
use UrlShortener\Generator\RandomCodeGenerator;

echo "🔍 URL Shortener Library - Installation Verification\n";
echo "====================================================\n\n";

$tests = [
    'Basic Functionality' => function() {
        $storage = new MemoryStorage();
        $generator = new RandomCodeGenerator();
        $shortener = new UrlShortener($storage, $generator);
        
        $testUrl = 'https://packagist.org/packages/richardowen/url-shortener';
        $shortCode = $shortener->shorten($testUrl);
        $expandedUrl = $shortener->expand($shortCode, false);
        
        return $expandedUrl === $testUrl;
    },
    
    'Analytics Tracking' => function() {
        $storage = new MemoryStorage();
        $generator = new RandomCodeGenerator();
        $shortener = new UrlShortener($storage, $generator);
        
        $testUrl = 'https://github.com/RichardOwen2/url-shortener-php';
        $shortCode = $shortener->shorten($testUrl);
        
        // Generate some clicks
        $shortener->expand($shortCode);
        $shortener->expand($shortCode);
        
        $analytics = $shortener->getAnalytics($shortCode);
        return $analytics['total_clicks'] === 2;
    },
    
    'URL Validation' => function() {
        $storage = new MemoryStorage();
        $generator = new RandomCodeGenerator();
        $shortener = new UrlShortener($storage, $generator);
        
        try {
            $shortener->shorten('not-a-valid-url');
            return false; // Should have thrown an exception
        } catch (\UrlShortener\Exception\InvalidUrlException $e) {
            return true; // Expected exception
        }
    },
    
    'Metadata Support' => function() {
        $storage = new MemoryStorage();
        $generator = new RandomCodeGenerator();
        $shortener = new UrlShortener($storage, $generator);
        
        $metadata = ['test' => true, 'version' => '1.0.0'];
        $shortCode = $shortener->shorten('https://example.com', null, $metadata);
        
        $record = $shortener->getUrlRecord($shortCode);
        return $record->getMetadata() === $metadata;
    },
    
    'Code Generation' => function() {
        $generator = new RandomCodeGenerator(8, 'ABCDEF123');
        $code = $generator->generate();
        
        // Check length
        if (strlen($code) !== 8) return false;
        
        // Check characters
        $allowedChars = 'ABCDEF123';
        for ($i = 0; $i < strlen($code); $i++) {
            if (strpos($allowedChars, $code[$i]) === false) {
                return false;
            }
        }
        
        return true;
    }
];

$passed = 0;
$total = count($tests);

foreach ($tests as $testName => $testFunction) {
    echo "Testing: {$testName}... ";
    
    try {
        if ($testFunction()) {
            echo "✅ PASS\n";
            $passed++;
        } else {
            echo "❌ FAIL\n";
        }
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Results: {$passed}/{$total} tests passed\n";

if ($passed === $total) {
    echo "🎉 All tests passed! Your installation is working correctly.\n";
    echo "\nNext steps:\n";
    echo "• Check out examples/basic_usage.php for more examples\n";
    echo "• Read the documentation in README.md\n";
    echo "• Start building with the URL Shortener library!\n";
} else {
    echo "⚠️  Some tests failed. Please check your installation.\n";
    echo "\nTry:\n";
    echo "• composer install --no-dev (for production)\n";
    echo "• composer install (for development)\n";
    echo "• Check PHP version (requires 8.0+)\n";
}

echo "\n";
