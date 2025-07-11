<?php

declare(strict_types=1);

namespace UrlShortener\Entity;

use DateTimeImmutable;

/**
 * Represents an analytics record for tracking URL clicks
 */
class AnalyticsRecord
{
    public function __construct(
        private string $shortCode,
        private DateTimeImmutable $clickedAt,
        private ?string $ipAddress = null,
        private ?string $userAgent = null,
        private ?string $referrer = null,
        private array $additionalData = []
    ) {
    }

    public function getShortCode(): string
    {
        return $this->shortCode;
    }

    public function getClickedAt(): DateTimeImmutable
    {
        return $this->clickedAt;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function getReferrer(): ?string
    {
        return $this->referrer;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
    }

    public function toArray(): array
    {
        return [
            'short_code' => $this->shortCode,
            'clicked_at' => $this->clickedAt->format('Y-m-d H:i:s'),
            'ip_address' => $this->ipAddress,
            'user_agent' => $this->userAgent,
            'referrer' => $this->referrer,
            'additional_data' => $this->additionalData,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['short_code'],
            new DateTimeImmutable($data['clicked_at']),
            $data['ip_address'] ?? null,
            $data['user_agent'] ?? null,
            $data['referrer'] ?? null,
            $data['additional_data'] ?? []
        );
    }
}
