<?php

declare(strict_types=1);

namespace UrlShortener;

use UrlShortener\Storage\StorageInterface;
use UrlShortener\Generator\GeneratorInterface;
use UrlShortener\Entity\UrlRecord;
use UrlShortener\Entity\AnalyticsRecord;
use UrlShortener\Exception\ShortCodeNotFoundException;
use UrlShortener\Exception\InvalidUrlException;
use DateTimeImmutable;

/**
 * Main URL Shortener class
 */
class UrlShortener
{
    private const MAX_GENERATION_ATTEMPTS = 10;

    public function __construct(
        private StorageInterface $storage,
        private GeneratorInterface $generator,
        private ?UrlValidator $validator = null
    ) {
        $this->validator = $validator ?? new UrlValidator();
    }

    /**
     * Shorten a URL and return the short code
     */
    public function shorten(
        string $url,
        ?DateTimeImmutable $expiresAt = null,
        array $metadata = []
    ): string {
        if (!$this->validator->isValid($url)) {
            throw new InvalidUrlException($url);
        }

        $shortCode = $this->generateUniqueCode();
        
        $record = new UrlRecord(
            $shortCode,
            $url,
            new DateTimeImmutable(),
            $expiresAt,
            0,
            $metadata
        );

        if (!$this->storage->store($record)) {
            throw new \RuntimeException('Failed to store URL record');
        }

        return $shortCode;
    }

    /**
     * Expand a short code to get the original URL
     */
    public function expand(string $shortCode, bool $trackClick = true): string
    {
        $record = $this->storage->retrieve($shortCode);

        if ($record === null) {
            throw new ShortCodeNotFoundException($shortCode);
        }

        if ($record->isExpired()) {
            throw new ShortCodeNotFoundException($shortCode);
        }

        if ($trackClick) {
            $this->trackClick($record);
        }

        return $record->getOriginalUrl();
    }

    /**
     * Check if a short code exists and is not expired
     */
    public function exists(string $shortCode): bool
    {
        $record = $this->storage->retrieve($shortCode);
        return $record !== null && !$record->isExpired();
    }

    /**
     * Delete a short code and its analytics
     */
    public function delete(string $shortCode): bool
    {
        return $this->storage->delete($shortCode);
    }

    /**
     * Get analytics for a short code
     */
    public function getAnalytics(string $shortCode): array
    {
        if (!$this->storage->exists($shortCode)) {
            throw new ShortCodeNotFoundException($shortCode);
        }

        $analytics = $this->storage->getAnalytics($shortCode);
        $record = $this->storage->retrieve($shortCode);

        return [
            'short_code' => $shortCode,
            'original_url' => $record?->getOriginalUrl(),
            'created_at' => $record?->getCreatedAt(),
            'expires_at' => $record?->getExpiresAt(),
            'total_clicks' => $record?->getClickCount() ?? 0,
            'clicks' => $analytics,
            'metadata' => $record?->getMetadata() ?? [],
        ];
    }

    /**
     * Get URL record information
     */
    public function getUrlRecord(string $shortCode): UrlRecord
    {
        $record = $this->storage->retrieve($shortCode);

        if ($record === null) {
            throw new ShortCodeNotFoundException($shortCode);
        }

        return $record;
    }

    /**
     * Update URL metadata
     */
    public function updateMetadata(string $shortCode, array $metadata): bool
    {
        $record = $this->storage->retrieve($shortCode);

        if ($record === null) {
            throw new ShortCodeNotFoundException($shortCode);
        }

        $updatedRecord = $record->withMetadata($metadata);
        return $this->storage->store($updatedRecord);
    }

    /**
     * Track a click on a short code
     */
    private function trackClick(UrlRecord $record): void
    {
        // Update click count
        $updatedRecord = $record->withClickCount($record->getClickCount() + 1);
        $this->storage->store($updatedRecord);

        // Store analytics record
        $analyticsRecord = new AnalyticsRecord(
            $record->getShortCode(),
            new DateTimeImmutable(),
            $this->getClientIp(),
            $this->getUserAgent(),
            $this->getReferrer()
        );

        $this->storage->storeAnalytics($analyticsRecord);
    }

    /**
     * Generate a unique short code
     */
    private function generateUniqueCode(): string
    {
        $attempts = 0;

        do {
            $code = $this->generator->generate();
            $attempts++;

            if ($attempts > self::MAX_GENERATION_ATTEMPTS) {
                throw new \RuntimeException('Unable to generate unique short code after maximum attempts');
            }
        } while ($this->storage->exists($code));

        return $code;
    }

    /**
     * Get client IP address (basic implementation)
     */
    private function getClientIp(): ?string
    {
        return $_SERVER['REMOTE_ADDR'] ?? null;
    }

    /**
     * Get user agent
     */
    private function getUserAgent(): ?string
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * Get referrer
     */
    private function getReferrer(): ?string
    {
        return $_SERVER['HTTP_REFERER'] ?? null;
    }
}
