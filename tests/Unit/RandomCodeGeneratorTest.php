<?php

declare(strict_types=1);

namespace UrlShortener\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UrlShortener\Generator\RandomCodeGenerator;

class RandomCodeGeneratorTest extends TestCase
{
    public function testGeneratesCodeWithCorrectLength(): void
    {
        $generator = new RandomCodeGenerator(8);
        $code = $generator->generate();
        
        $this->assertIsString($code);
        $this->assertEquals(8, strlen($code));
    }

    public function testGeneratesUniqueCodesOnMultipleCalls(): void
    {
        $generator = new RandomCodeGenerator(6);
        $codes = [];
        
        for ($i = 0; $i < 100; $i++) {
            $codes[] = $generator->generate();
        }
        
        // Check that all codes are unique
        $uniqueCodes = array_unique($codes);
        $this->assertCount(100, $uniqueCodes);
    }

    public function testUsesCustomCharacters(): void
    {
        $customChars = '123456789';
        $generator = new RandomCodeGenerator(10, $customChars);
        $code = $generator->generate();
        
        $this->assertEquals(10, strlen($code));
        
        // Check that all characters in the code are from our custom set
        for ($i = 0; $i < strlen($code); $i++) {
            $this->assertStringContainsString($code[$i], $customChars);
        }
    }

    public function testThrowsExceptionForInvalidLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Code length must be at least 1');
        
        new RandomCodeGenerator(0);
    }

    public function testThrowsExceptionForEmptyCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Characters cannot be empty');
        
        new RandomCodeGenerator(6, '');
    }

    public function testGettersReturnCorrectValues(): void
    {
        $length = 7;
        $characters = 'abcdef123';
        $generator = new RandomCodeGenerator($length, $characters);
        
        $this->assertEquals($length, $generator->getLength());
        $this->assertEquals($characters, $generator->getCharacters());
    }
}
