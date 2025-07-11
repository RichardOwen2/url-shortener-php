<?php

declare(strict_types=1);

namespace UrlShortener\Storage;

use UrlShortener\Entity\UrlRecord;
use UrlShortener\Entity\AnalyticsRecord;

/**
 * In-memory storage implementation (for testing and development)
 */
class MemoryStorage implements StorageInterface
{
    /** @var array<string, UrlRecord> */
    private array $urls = [];

    /** @var array<string, AnalyticsRecord[]> */
    private array $analytics = [];

    public function store(UrlRecord $record): bool
    {
        $this->urls[$record->getShortCode()] = $record;
        return true;
    }

    public function retrieve(string $shortCode): ?UrlRecord
    {
        return $this->urls[$shortCode] ?? null;
    }

    public function exists(string $shortCode): bool
    {
        return isset($this->urls[$shortCode]);
    }

    public function delete(string $shortCode): bool
    {
        if (isset($this->urls[$shortCode])) {
            unset($this->urls[$shortCode]);
            unset($this->analytics[$shortCode]);
            return true;
        }
        return false;
    }

    public function storeAnalytics(AnalyticsRecord $record): bool
    {
        $shortCode = $record->getShortCode();
        
        if (!isset($this->analytics[$shortCode])) {
            $this->analytics[$shortCode] = [];
        }
        
        $this->analytics[$shortCode][] = $record;
        return true;
    }

    public function getAnalytics(string $shortCode): array
    {
        return $this->analytics[$shortCode] ?? [];
    }

    /**
     * Clear all stored data (useful for testing)
     */
    public function clear(): void
    {
        $this->urls = [];
        $this->analytics = [];
    }

    /**
     * Get all stored URLs (useful for debugging)
     */
    public function getAllUrls(): array
    {
        return $this->urls;
    }
}
