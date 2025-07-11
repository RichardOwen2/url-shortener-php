<?php

declare(strict_types=1);

namespace UrlShortener\Storage;

use UrlShortener\Entity\UrlRecord;
use UrlShortener\Entity\AnalyticsRecord;
use UrlShortener\Exception\UrlShortenerException;
use RuntimeException;

/**
 * File-based storage implementation
 */
class FileStorage implements StorageInterface
{
    private string $urlsFile;
    private string $analyticsFile;

    public function __construct(string $dataDirectory)
    {
        if (!is_dir($dataDirectory)) {
            if (!mkdir($dataDirectory, 0755, true)) {
                throw new UrlShortenerException("Cannot create data directory: {$dataDirectory}");
            }
        }

        if (!is_writable($dataDirectory)) {
            throw new UrlShortenerException("Data directory is not writable: {$dataDirectory}");
        }

        $this->urlsFile = rtrim($dataDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'urls.json';
        $this->analyticsFile = rtrim($dataDirectory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'analytics.json';
    }

    public function store(UrlRecord $record): bool
    {
        $urls = $this->loadUrls();
        $urls[$record->getShortCode()] = $record->toArray();
        return $this->saveUrls($urls);
    }

    public function retrieve(string $shortCode): ?UrlRecord
    {
        $urls = $this->loadUrls();
        
        if (!isset($urls[$shortCode])) {
            return null;
        }

        return UrlRecord::fromArray($urls[$shortCode]);
    }

    public function exists(string $shortCode): bool
    {
        $urls = $this->loadUrls();
        return isset($urls[$shortCode]);
    }

    public function delete(string $shortCode): bool
    {
        $urls = $this->loadUrls();
        $analytics = $this->loadAnalytics();

        if (isset($urls[$shortCode])) {
            unset($urls[$shortCode]);
            unset($analytics[$shortCode]);
            
            return $this->saveUrls($urls) && $this->saveAnalytics($analytics);
        }

        return false;
    }

    public function storeAnalytics(AnalyticsRecord $record): bool
    {
        $analytics = $this->loadAnalytics();
        $shortCode = $record->getShortCode();

        if (!isset($analytics[$shortCode])) {
            $analytics[$shortCode] = [];
        }

        $analytics[$shortCode][] = $record->toArray();
        return $this->saveAnalytics($analytics);
    }

    public function getAnalytics(string $shortCode): array
    {
        $analytics = $this->loadAnalytics();
        $records = $analytics[$shortCode] ?? [];

        return array_map(
            fn(array $data) => AnalyticsRecord::fromArray($data),
            $records
        );
    }

    private function loadUrls(): array
    {
        if (!file_exists($this->urlsFile)) {
            return [];
        }

        $content = file_get_contents($this->urlsFile);
        
        if ($content === false) {
            throw new RuntimeException("Cannot read URLs file: {$this->urlsFile}");
        }

        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in URLs file: " . json_last_error_msg());
        }

        return $data ?? [];
    }

    private function saveUrls(array $urls): bool
    {
        $content = json_encode($urls, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if ($content === false) {
            throw new RuntimeException("Cannot encode URLs to JSON: " . json_last_error_msg());
        }

        return file_put_contents($this->urlsFile, $content) !== false;
    }

    private function loadAnalytics(): array
    {
        if (!file_exists($this->analyticsFile)) {
            return [];
        }

        $content = file_get_contents($this->analyticsFile);
        
        if ($content === false) {
            throw new RuntimeException("Cannot read analytics file: {$this->analyticsFile}");
        }

        $data = json_decode($content, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON in analytics file: " . json_last_error_msg());
        }

        return $data ?? [];
    }

    private function saveAnalytics(array $analytics): bool
    {
        $content = json_encode($analytics, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        
        if ($content === false) {
            throw new RuntimeException("Cannot encode analytics to JSON: " . json_last_error_msg());
        }

        return file_put_contents($this->analyticsFile, $content) !== false;
    }
}
