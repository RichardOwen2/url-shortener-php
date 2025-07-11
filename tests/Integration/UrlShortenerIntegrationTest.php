<?php

declare(strict_types=1);

namespace UrlShortener\Tests\Integration;

use PHPUnit\Framework\TestCase;
use UrlShortener\UrlShortener;
use UrlShortener\Storage\MemoryStorage;
use UrlShortener\Generator\RandomCodeGenerator;
use UrlShortener\Generator\SequentialCodeGenerator;
use UrlShortener\Exception\ShortCodeNotFoundException;
use UrlShortener\Exception\InvalidUrlException;
use DateTimeImmutable;

class UrlShortenerIntegrationTest extends TestCase
{
    private UrlShortener $shortener;
    private MemoryStorage $storage;

    protected function setUp(): void
    {
        $this->storage = new MemoryStorage();
        $generator = new RandomCodeGenerator();
        $this->shortener = new UrlShortener($this->storage, $generator);
    }

    public function testCompleteUrlShorteningWorkflow(): void
    {
        $originalUrl = 'https://www.example.com/very/long/url';
        $metadata = ['source' => 'test', 'campaign' => 'integration'];

        // Shorten URL
        $shortCode = $this->shortener->shorten($originalUrl, null, $metadata);
        $this->assertNotEmpty($shortCode);

        // Verify it exists
        $this->assertTrue($this->shortener->exists($shortCode));

        // Expand URL
        $expandedUrl = $this->shortener->expand($shortCode);
        $this->assertEquals($originalUrl, $expandedUrl);

        // Check analytics
        $analytics = $this->shortener->getAnalytics($shortCode);
        $this->assertEquals(1, $analytics['total_clicks']);
        $this->assertEquals($originalUrl, $analytics['original_url']);
        $this->assertEquals($metadata, $analytics['metadata']);
    }

    public function testUrlWithExpiration(): void
    {
        $url = 'https://example.com/expires-soon';
        $expiresAt = new DateTimeImmutable('+1 hour');
        
        $shortCode = $this->shortener->shorten($url, $expiresAt);
        
        // Should work before expiration
        $this->assertEquals($url, $this->shortener->expand($shortCode, false));
        
        // Test with past expiration date
        $pastExpiration = new DateTimeImmutable('-1 hour');
        $expiredCode = $this->shortener->shorten('https://example.com/expired', $pastExpiration);
        
        $this->expectException(ShortCodeNotFoundException::class);
        $this->shortener->expand($expiredCode);
    }

    public function testMultipleClicks(): void
    {
        $url = 'https://example.com/popular-page';
        $shortCode = $this->shortener->shorten($url);
        
        // Simulate multiple clicks
        $clickCount = 5;
        for ($i = 0; $i < $clickCount; $i++) {
            $this->shortener->expand($shortCode);
        }
        
        $analytics = $this->shortener->getAnalytics($shortCode);
        $this->assertEquals($clickCount, $analytics['total_clicks']);
        $this->assertCount($clickCount, $analytics['clicks']);
    }

    public function testInvalidUrlHandling(): void
    {
        $this->expectException(InvalidUrlException::class);
        $this->shortener->shorten('not-a-valid-url');
    }

    public function testNonExistentCodeHandling(): void
    {
        $this->expectException(ShortCodeNotFoundException::class);
        $this->shortener->expand('nonexistent123');
    }

    public function testMetadataUpdating(): void
    {
        $url = 'https://example.com/updateable';
        $originalMetadata = ['version' => 1];
        $shortCode = $this->shortener->shorten($url, null, $originalMetadata);
        
        $newMetadata = ['version' => 2, 'updated' => true];
        $this->assertTrue($this->shortener->updateMetadata($shortCode, $newMetadata));
        
        $record = $this->shortener->getUrlRecord($shortCode);
        $this->assertEquals($newMetadata, $record->getMetadata());
    }

    public function testUrlDeletion(): void
    {
        $url = 'https://example.com/to-be-deleted';
        $shortCode = $this->shortener->shorten($url);
        
        // Verify it exists
        $this->assertTrue($this->shortener->exists($shortCode));
        
        // Delete it
        $this->assertTrue($this->shortener->delete($shortCode));
        
        // Verify it's gone
        $this->assertFalse($this->shortener->exists($shortCode));
        
        // Should throw exception when trying to expand
        $this->expectException(ShortCodeNotFoundException::class);
        $this->shortener->expand($shortCode);
    }

    public function testDifferentGenerators(): void
    {
        // Test with sequential generator
        $sequentialGenerator = new SequentialCodeGenerator('test', 3);
        $sequentialShortener = new UrlShortener($this->storage, $sequentialGenerator);
        
        $url1 = 'https://example.com/first';
        $url2 = 'https://example.com/second';
        
        $code1 = $sequentialShortener->shorten($url1);
        $code2 = $sequentialShortener->shorten($url2);
        
        $this->assertEquals('test001', $code1);
        $this->assertEquals('test002', $code2);
        
        $this->assertEquals($url1, $sequentialShortener->expand($code1, false));
        $this->assertEquals($url2, $sequentialShortener->expand($code2, false));
    }

    public function testConcurrentOperations(): void
    {
        $urls = [
            'https://example.com/page1',
            'https://example.com/page2',
            'https://example.com/page3',
        ];
        
        $shortCodes = [];
        
        // Shorten multiple URLs
        foreach ($urls as $url) {
            $shortCodes[] = $this->shortener->shorten($url);
        }
        
        // Verify all can be expanded
        foreach ($shortCodes as $index => $shortCode) {
            $expandedUrl = $this->shortener->expand($shortCode, false);
            $this->assertEquals($urls[$index], $expandedUrl);
        }
        
        // Verify all exist
        foreach ($shortCodes as $shortCode) {
            $this->assertTrue($this->shortener->exists($shortCode));
        }
    }
}
