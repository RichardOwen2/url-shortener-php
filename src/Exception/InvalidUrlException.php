<?php

declare(strict_types=1);

namespace UrlShortener\Exception;

/**
 * Exception thrown when URL validation fails
 */
class InvalidUrlException extends UrlShortenerException
{
    public function __construct(string $url)
    {
        parent::__construct("Invalid URL: '{$url}'");
    }
}
