<?php

declare(strict_types=1);

namespace UrlShortener\Generator;

/**
 * Sequential code generator that produces codes like: abc001, abc002, etc.
 */
class SequentialCodeGenerator implements GeneratorInterface
{
    private int $counter = 1;

    public function __construct(
        private string $prefix = '',
        private int $padding = 3,
        int $startFrom = 1
    ) {
        if ($padding < 1) {
            throw new \InvalidArgumentException('Padding must be at least 1');
        }

        if ($startFrom < 1) {
            throw new \InvalidArgumentException('Start from must be at least 1');
        }

        $this->counter = $startFrom;
    }

    public function generate(): string
    {
        $code = $this->prefix . str_pad((string) $this->counter, $this->padding, '0', STR_PAD_LEFT);
        $this->counter++;
        
        return $code;
    }

    public function getCounter(): int
    {
        return $this->counter;
    }

    public function setCounter(int $counter): void
    {
        if ($counter < 1) {
            throw new \InvalidArgumentException('Counter must be at least 1');
        }

        $this->counter = $counter;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getPadding(): int
    {
        return $this->padding;
    }
}
