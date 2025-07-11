<?php

declare(strict_types=1);

namespace UrlShortener\Generator;

/**
 * Random code generator that produces alphanumeric short codes
 */
class RandomCodeGenerator implements GeneratorInterface
{
    private const CHARACTERS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

    public function __construct(
        private int $length = 6,
        private string $characters = self::CHARACTERS
    ) {
        if ($length < 1) {
            throw new \InvalidArgumentException('Code length must be at least 1');
        }

        if (empty($characters)) {
            throw new \InvalidArgumentException('Characters cannot be empty');
        }
    }

    public function generate(): string
    {
        $code = '';
        $charactersLength = strlen($this->characters);

        for ($i = 0; $i < $this->length; $i++) {
            $code .= $this->characters[random_int(0, $charactersLength - 1)];
        }

        return $code;
    }

    public function getLength(): int
    {
        return $this->length;
    }

    public function getCharacters(): string
    {
        return $this->characters;
    }
}
