<?php

declare(strict_types=1);

namespace UrlShortener\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UrlShortener\UrlValidator;

class UrlValidatorTest extends TestCase
{
    private UrlValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new UrlValidator();
    }

    public function testValidatesCorrectUrls(): void
    {
        $validUrls = [
            'https://www.example.com',
            'http://example.com',
            'https://subdomain.example.com/path',
            'http://example.com:8080/path?query=value',
            'https://example.com/path#fragment',
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue($this->validator->isValid($url), "URL should be valid: {$url}");
        }
    }

    public function testRejectsInvalidUrls(): void
    {
        $invalidUrls = [
            'not-a-url',
            'ftp://example.com', // Not allowed by default
            'http://',
            'https://',
            '',
            'javascript:alert("xss")',
        ];

        foreach ($invalidUrls as $url) {
            $this->assertFalse($this->validator->isValid($url), "URL should be invalid: {$url}");
        }
    }

    public function testRejectsUrlsTooLong(): void
    {
        $validator = new UrlValidator(['http', 'https'], 50);
        $longUrl = 'https://example.com/' . str_repeat('a', 100);
        
        $this->assertFalse($validator->isValid($longUrl));
    }

    public function testCustomAllowedSchemes(): void
    {
        $validator = new UrlValidator(['ftp', 'sftp']);
        
        $this->assertTrue($validator->isValid('ftp://example.com'));
        $this->assertTrue($validator->isValid('sftp://example.com'));
        $this->assertFalse($validator->isValid('http://example.com'));
    }

    public function testNormalizeUrl(): void
    {
        $testCases = [
            'example.com' => 'http://example.com',
            'EXAMPLE.COM' => 'http://example.com',
            'https://EXAMPLE.COM/PATH' => 'https://example.com/PATH',
        ];

        foreach ($testCases as $input => $expected) {
            $this->assertEquals($expected, $this->validator->normalize($input));
        }
    }

    public function testGetters(): void
    {
        $schemes = ['http', 'https', 'ftp'];
        $maxLength = 1000;
        
        $validator = new UrlValidator($schemes, $maxLength);
        
        $this->assertEquals($schemes, $validator->getAllowedSchemes());
        $this->assertEquals($maxLength, $validator->getMaxLength());
    }
}
