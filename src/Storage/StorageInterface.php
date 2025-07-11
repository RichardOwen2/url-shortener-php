<?php

declare(strict_types=1);

namespace UrlShortener\Storage;

use UrlShortener\Entity\UrlRecord;
use UrlShortener\Entity\AnalyticsRecord;

/**
 * Interface for URL storage implementations
 */
interface StorageInterface
{
    /**
     * Store a URL record
     */
    public function store(UrlRecord $record): bool;

    /**
     * Retrieve a URL record by short code
     */
    public function retrieve(string $shortCode): ?UrlRecord;

    /**
     * Check if a short code exists
     */
    public function exists(string $shortCode): bool;

    /**
     * Delete a URL record
     */
    public function delete(string $shortCode): bool;

    /**
     * Store an analytics record
     */
    public function storeAnalytics(AnalyticsRecord $record): bool;

    /**
     * Retrieve analytics for a short code
     */
    public function getAnalytics(string $shortCode): array;
}
