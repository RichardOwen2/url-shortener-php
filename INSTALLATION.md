# Installation Guide

## 📥 How to Download and Use This Library

### Method 1: Composer (Recommended)

Once published to Packagist, users can install via Composer:

```bash
composer require richardowen/url-shortener
```

### Method 2: Git Clone (Development)

```bash
git clone https://github.com/RichardOwen2/url-shortener-php.git
cd url-shortener-php
composer install
```

### Method 3: Download ZIP

Download the latest release from GitHub and extract:

```bash
# Download and extract
wget https://github.com/RichardOwen2/url-shortener-php/archive/v1.0.0.zip
unzip v1.0.0.zip
cd url-shortener-php-1.0.0
composer install
```

## 🚀 Quick Start After Installation

### Basic Usage

```php
<?php
require_once 'vendor/autoload.php';

use UrlShortener\UrlShortener;
use UrlShortener\Storage\FileStorage;
use UrlShortener\Generator\RandomCodeGenerator;

// Initialize
$storage = new FileStorage(__DIR__ . '/data');
$generator = new RandomCodeGenerator();
$shortener = new UrlShortener($storage, $generator);

// Use it
$shortCode = $shortener->shorten('https://www.example.com/very/long/url');
echo "Short code: " . $shortCode . "\n";

$originalUrl = $shortener->expand($shortCode);
echo "Original URL: " . $originalUrl . "\n";
```

### Test Installation

```bash
# Run the quick start script
php vendor/richardowen/url-shortener/quick_start.php

# Or if you cloned the repo
php quick_start.php
```

## 📋 Requirements

- **PHP**: 8.0 or higher
- **Composer**: For dependency management
- **Extensions**: 
  - `json` (usually included)
  - `pdo` (for database storage)

## 🗂️ Storage Setup

### File Storage
```php
// Will create directory if it doesn't exist
$storage = new FileStorage(__DIR__ . '/url_data');
```

### Database Storage
```php
// MySQL/PostgreSQL setup required
$pdo = new PDO('mysql:host=localhost;dbname=urlshortener', $username, $password);
$storage = new DatabaseStorage($pdo);
```

### Memory Storage (Testing)
```php
// No setup required - data is lost when script ends
$storage = new MemoryStorage();
```

## 🔧 Configuration Examples

### Custom Code Generator
```php
// 8-character codes using only numbers
$generator = new RandomCodeGenerator(8, '0123456789');

// Sequential codes with prefix
$generator = new SequentialCodeGenerator('link', 4); // link0001, link0002, etc.
```

### URL with Expiration
```php
$expiresAt = new DateTimeImmutable('+7 days');
$shortCode = $shortener->shorten($url, $expiresAt);
```

### URL with Metadata
```php
$metadata = ['campaign' => 'summer2025', 'source' => 'email'];
$shortCode = $shortener->shorten($url, null, $metadata);
```

## 📊 Analytics Usage

```php
// Get detailed analytics
$analytics = $shortener->getAnalytics($shortCode);

echo "Total clicks: " . $analytics['total_clicks'] . "\n";
echo "Created: " . $analytics['created_at']->format('Y-m-d H:i:s') . "\n";

// Individual click records
foreach ($analytics['clicks'] as $click) {
    echo "Clicked at: " . $click->getClickedAt()->format('Y-m-d H:i:s') . "\n";
    echo "User agent: " . $click->getUserAgent() . "\n";
}
```

## 🧪 Running Tests

```bash
# Install dev dependencies
composer install

# Run tests
composer test

# Check code quality
composer analyse
composer cs-check
```

## 📖 Further Documentation

- See `examples/` directory for more usage examples
- Check `README.md` for complete API documentation
- Review `ARCHITECTURE.md` for technical details
