<?php

declare(strict_types=1);

namespace UrlShortener\Exception;

/**
 * Exception thrown when a short code is not found
 */
class ShortCodeNotFoundException extends UrlShortenerException
{
    public function __construct(string $shortCode)
    {
        parent::__construct("Short code '{$shortCode}' not found.");
    }
}
