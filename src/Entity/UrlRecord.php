<?php

declare(strict_types=1);

namespace UrlShortener\Entity;

use DateTime;
use DateTimeImmutable;

/**
 * Represents a URL record with metadata
 */
class UrlRecord
{
    public function __construct(
        private string $shortCode,
        private string $originalUrl,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $expiresAt = null,
        private int $clickCount = 0,
        private array $metadata = []
    ) {
    }

    public function getShortCode(): string
    {
        return $this->shortCode;
    }

    public function getOriginalUrl(): string
    {
        return $this->originalUrl;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function getClickCount(): int
    {
        return $this->clickCount;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function incrementClickCount(): void
    {
        $this->clickCount++;
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return new DateTimeImmutable() > $this->expiresAt;
    }

    public function withClickCount(int $clickCount): self
    {
        $new = clone $this;
        $new->clickCount = $clickCount;
        return $new;
    }

    public function withMetadata(array $metadata): self
    {
        $new = clone $this;
        $new->metadata = $metadata;
        return $new;
    }

    public function toArray(): array
    {
        return [
            'short_code' => $this->shortCode,
            'original_url' => $this->originalUrl,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'expires_at' => $this->expiresAt?->format('Y-m-d H:i:s'),
            'click_count' => $this->clickCount,
            'metadata' => $this->metadata,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['short_code'],
            $data['original_url'],
            new DateTimeImmutable($data['created_at']),
            isset($data['expires_at']) ? new DateTimeImmutable($data['expires_at']) : null,
            $data['click_count'] ?? 0,
            $data['metadata'] ?? []
        );
    }
}
