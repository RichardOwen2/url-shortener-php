<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use UrlShortener\UrlShortener;
use UrlShortener\Storage\MemoryStorage;
use UrlShortener\Storage\FileStorage;
use UrlShortener\Generator\RandomCodeGenerator;
use UrlShortener\Generator\SequentialCodeGenerator;

echo "=== URL Shortener Library - Basic Example ===\n\n";

// Example 1: Basic usage with memory storage
echo "1. Basic Memory Storage Example:\n";
echo "--------------------------------\n";

$memoryStorage = new MemoryStorage();
$randomGenerator = new RandomCodeGenerator(6);
$shortener = new UrlShortener($memoryStorage, $randomGenerator);

// Shorten some URLs
$urls = [
    'https://www.example.com/very/long/url/that/needs/shortening',
    'https://github.com/example/repository',
    'https://stackoverflow.com/questions/12345678/long-question-title'
];

$shortCodes = [];

foreach ($urls as $url) {
    $shortCode = $shortener->shorten($url);
    $shortCodes[] = $shortCode;
    echo "Shortened: {$url}\n";
    echo "Short code: {$shortCode}\n";
    echo "Expanded: " . $shortener->expand($shortCode) . "\n\n";
}

// Example 2: File storage with sequential generator
echo "2. File Storage with Sequential Generator:\n";
echo "-----------------------------------------\n";

$fileStorage = new FileStorage(__DIR__ . '/data');
$sequentialGenerator = new SequentialCodeGenerator('url', 4);
$fileShortener = new UrlShortener($fileStorage, $sequentialGenerator);

$testUrl = 'https://www.google.com/search?q=url+shortener';
$shortCode = $fileShortener->shorten($testUrl);

echo "Original URL: {$testUrl}\n";
echo "Short code: {$shortCode}\n";
echo "Expanded: " . $fileShortener->expand($shortCode) . "\n\n";

// Example 3: Analytics demonstration
echo "3. Analytics Example:\n";
echo "--------------------\n";

// Simulate multiple clicks
for ($i = 0; $i < 5; $i++) {
    $shortener->expand($shortCodes[0], true); // Track clicks
    usleep(100000); // Small delay to show different timestamps
}

$analytics = $shortener->getAnalytics($shortCodes[0]);
echo "Analytics for short code: {$shortCodes[0]}\n";
echo "Total clicks: {$analytics['total_clicks']}\n";
echo "Original URL: {$analytics['original_url']}\n";
echo "Created at: " . $analytics['created_at']->format('Y-m-d H:i:s') . "\n";
echo "Number of click records: " . count($analytics['clicks']) . "\n\n";

// Example 4: URL with expiration and metadata
echo "4. URL with Expiration and Metadata:\n";
echo "------------------------------------\n";

$expirationTime = new DateTimeImmutable('+1 hour');
$metadata = [
    'campaign' => 'summer_sale',
    'source' => 'email',
    'user_id' => 12345
];

$expirableUrl = 'https://shop.example.com/summer-sale';
$expirableCode = $shortener->shorten($expirableUrl, $expirationTime, $metadata);

echo "URL with expiration: {$expirableUrl}\n";
echo "Short code: {$expirableCode}\n";
echo "Expires at: " . $expirationTime->format('Y-m-d H:i:s') . "\n";
echo "Metadata: " . json_encode($metadata, JSON_PRETTY_PRINT) . "\n\n";

$recordInfo = $shortener->getUrlRecord($expirableCode);
echo "Retrieved metadata: " . json_encode($recordInfo->getMetadata(), JSON_PRETTY_PRINT) . "\n\n";

// Example 5: Error handling
echo "5. Error Handling Example:\n";
echo "--------------------------\n";

try {
    $shortener->expand('nonexistent');
} catch (\UrlShortener\Exception\ShortCodeNotFoundException $e) {
    echo "Caught expected exception: " . $e->getMessage() . "\n";
}

try {
    $shortener->shorten('not-a-valid-url');
} catch (\UrlShortener\Exception\InvalidUrlException $e) {
    echo "Caught expected exception: " . $e->getMessage() . "\n";
}

echo "\n=== Example completed successfully! ===\n";
