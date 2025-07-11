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

### Via Composer (Recommended)

```bash
composer require richardowen/url-shortener
```

### Alternative Installation Methods

#### From GitHub (Development Version)
```bash
git clone https://github.com/RichardOwen2/url-shortener-php.git
cd url-shortener-php
composer install
```

#### Specific Version
```bash
composer require richardowen/url-shortener:^1.0
```

### Requirements

- **PHP**: 8.0 or higher
- **Composer**: For dependency management
- **Extensions**: `json`, `pdo` (for database storage)

### Verify Installation

After installation, create a test file to verify everything works:

```php
<?php
// test.php
require_once 'vendor/autoload.php';

use UrlShortener\UrlShortener;
use UrlShortener\Storage\MemoryStorage;
use UrlShortener\Generator\RandomCodeGenerator;

echo "🚀 Testing URL Shortener Library...\n";

try {
    $storage = new MemoryStorage();
    $generator = new RandomCodeGenerator();
    $shortener = new UrlShortener($storage, $generator);
    
    $testUrl = 'https://packagist.org/packages/richardowen/url-shortener';
    $shortCode = $shortener->shorten($testUrl);
    $expandedUrl = $shortener->expand($shortCode);
    
    if ($expandedUrl === $testUrl) {
        echo "✅ Installation successful!\n";
        echo "Short code: {$shortCode}\n";
        echo "Original URL: {$expandedUrl}\n";
    } else {
        echo "❌ Something went wrong!\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
```

Run the test:
```bash
php test.php
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

## Getting Help

### Documentation
- 📖 [Complete Installation Guide](INSTALLATION.md)
- 🏗️ [Architecture Overview](ARCHITECTURE.md)
- 💡 [Examples Directory](examples/)

### Quick Links
- 📦 [Packagist Package](https://packagist.org/packages/richardowen/url-shortener)
- 🐙 [GitHub Repository](https://github.com/RichardOwen2/url-shortener-php)
- 🐛 [Report Issues](https://github.com/RichardOwen2/url-shortener-php/issues)

### Common Use Cases

#### Marketing Campaign Tracking
```php
$metadata = ['campaign' => 'summer2025', 'source' => 'email'];
$shortCode = $shortener->shorten($campaignUrl, null, $metadata);
```

#### Temporary Links
```php
$expiresAt = new DateTimeImmutable('+7 days');
$shortCode = $shortener->shorten($temporaryUrl, $expiresAt);
```

#### Social Media Sharing
```php
$socialUrl = 'https://blog.example.com/how-to-build-url-shortener';
$shortCode = $shortener->shorten($socialUrl);
echo "Share: https://yourdomain.com/" . $shortCode;
```

## Development

### Running Tests
```bash
composer install
composer test
```

### Code Quality
```bash
composer analyse    # PHPStan analysis
composer cs-check   # Code style check
composer cs-fix     # Fix code style
```

## Requirements

- PHP 8.0 or higher
- PSR-4 autoloader support
- Extensions: `json`, `pdo` (for database storage)

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## License

MIT License - see [LICENSE](LICENSE) file for details.
