<?php

declare(strict_types=1);

namespace UrlShortener\Generator;

/**
 * Interface for short code generators
 */
interface GeneratorInterface
{
    /**
     * Generate a unique short code
     */
    public function generate(): string;
}
