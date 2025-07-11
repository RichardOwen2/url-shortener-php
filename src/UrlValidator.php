<?php

declare(strict_types=1);

namespace UrlShortener;

/**
 * URL validation utility
 */
class UrlValidator
{
    private array $allowedSchemes;
    private int $maxLength;

    public function __construct(
        array $allowedSchemes = ['http', 'https'],
        int $maxLength = 2048
    ) {
        $this->allowedSchemes = $allowedSchemes;
        $this->maxLength = $maxLength;
    }

    /**
     * Validate if a URL is valid
     */
    public function isValid(string $url): bool
    {
        // Check length
        if (strlen($url) > $this->maxLength) {
            return false;
        }

        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check scheme
        $parsedUrl = parse_url($url);
        if (!isset($parsedUrl['scheme'])) {
            return false;
        }

        if (!in_array(strtolower($parsedUrl['scheme']), $this->allowedSchemes, true)) {
            return false;
        }

        // Check if host exists
        if (!isset($parsedUrl['host']) || empty($parsedUrl['host'])) {
            return false;
        }

        return true;
    }

    /**
     * Normalize URL (add scheme if missing, remove trailing slash, etc.)
     */
    public function normalize(string $url): string
    {
        $url = trim($url);

        // Add scheme if missing
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'http://' . $url;
        }

        // Parse and rebuild URL to normalize it
        $parts = parse_url($url);
        
        if (!$parts) {
            return $url;
        }

        $normalized = '';
        
        if (isset($parts['scheme'])) {
            $normalized .= strtolower($parts['scheme']) . '://';
        }

        if (isset($parts['host'])) {
            $normalized .= strtolower($parts['host']);
        }

        if (isset($parts['port']) && $parts['port'] !== 80 && $parts['port'] !== 443) {
            $normalized .= ':' . $parts['port'];
        }

        if (isset($parts['path'])) {
            $normalized .= $parts['path'];
        }

        if (isset($parts['query'])) {
            $normalized .= '?' . $parts['query'];
        }

        if (isset($parts['fragment'])) {
            $normalized .= '#' . $parts['fragment'];
        }

        return $normalized;
    }

    /**
     * Get allowed schemes
     */
    public function getAllowedSchemes(): array
    {
        return $this->allowedSchemes;
    }

    /**
     * Get maximum URL length
     */
    public function getMaxLength(): int
    {
        return $this->maxLength;
    }
}
