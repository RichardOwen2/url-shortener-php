# URL Shortener PHP Library

A comprehensive, well-structured PHP library for URL shortening with analytics tracking and multiple storage backends.

## Features

- **URL Shortening & Expansion**: Convert long URLs to short codes and vice versa
- **Analytics Tracking**: Track clicks, referrers, user agents, and timestamps
- **Multiple Storage Backends**: Memory, File, and Database storage options
- **Flexible Code Generation**: Custom strategies for generating short codes
- **PSR Compliance**: Follows PSR-4 autoloading and PSR-12 coding standards
- **Dependency Injection**: Clean architecture with SOLID principles
- **Extensible Design**: Easy to add new storage backends and features

## Installation

```bash
composer require urlshortener/php-library
```

## Quick Start

```php
<?php
require_once 'vendor/autoload.php';

use UrlShortener\UrlShortener;
use UrlShortener\Storage\FileStorage;
use UrlShortener\Generator\RandomCodeGenerator;

// Create storage and code generator
$storage = new FileStorage(__DIR__ . '/data');
$generator = new RandomCodeGenerator();

// Initialize the URL shortener
$shortener = new UrlShortener($storage, $generator);

// Shorten a URL
$shortCode = $shortener->shorten('https://www.example.com/very/long/url');
echo "Short code: " . $shortCode . "\n";

// Expand the short code
$originalUrl = $shortener->expand($shortCode);
echo "Original URL: " . $originalUrl . "\n";

// Get analytics
$analytics = $shortener->getAnalytics($shortCode);
print_r($analytics);
```

## Storage Backends

### File Storage
```php
use UrlShortener\Storage\FileStorage;

$storage = new FileStorage('/path/to/data/directory');
```

### Memory Storage (for testing)
```php
use UrlShortener\Storage\MemoryStorage;

$storage = new MemoryStorage();
```

### Database Storage
```php
use UrlShortener\Storage\DatabaseStorage;

$pdo = new PDO('mysql:host=localhost;dbname=url_shortener', $username, $password);
$storage = new DatabaseStorage($pdo);
```

## Code Generators

### Random Code Generator
```php
use UrlShortener\Generator\RandomCodeGenerator;

$generator = new RandomCodeGenerator(6); // 6 character codes
```

### Custom Code Generator
```php
use UrlShortener\Generator\GeneratorInterface;

class CustomGenerator implements GeneratorInterface
{
    public function generate(): string
    {
        // Your custom logic here
        return 'custom-' . uniqid();
    }
}
```

## Analytics

Track and retrieve analytics for shortened URLs:

```php
// Get basic analytics
$analytics = $shortener->getAnalytics($shortCode);

// Analytics include:
// - Total clicks
// - Click timestamps
// - Referrer information
// - User agent data
// - Geographic data (if implemented)
```

## Architecture

The library follows SOLID principles and uses dependency injection:

- **Single Responsibility**: Each class has one clear purpose
- **Open/Closed**: Easy to extend with new storage backends and generators
- **Liskov Substitution**: All implementations are interchangeable
- **Interface Segregation**: Clean, focused interfaces
- **Dependency Inversion**: Depends on abstractions, not concretions

## Requirements

- PHP 8.0 or higher
- PSR-4 autoloader support

## License

MIT License - see LICENSE file for details.
