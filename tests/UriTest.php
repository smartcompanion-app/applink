<?php

declare(strict_types=1);

namespace SmartCompanion\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartCompanion\Uri;

class UriTest extends TestCase
{
    public function testAppFromPath(): void
    {
        $uri = new Uri('/app1');
        $this->assertSame('app1', $uri->getApp());
    }

    public function testAppFromQuery(): void
    {
        $uri = new Uri('/?app=app2');
        $this->assertSame('app2', $uri->getApp());
    }

    public function testAppFromPathAndQuery(): void
    {
        $uri = new Uri('/app3?app=app4');
        $this->assertSame('app3', $uri->getApp());
    }

    public function testIncorrectPath(): void
    {
        $uri = new Uri('/  %%&$%?x=y');
        $this->assertSame('', $uri->getApp());
    }

    public function testMalformedUri(): void
    {
        $uri = new Uri('http:///nope');
        $this->assertSame('', $uri->getApp());
        $this->assertSame('/', $uri->getPath());
    }

    public function testQueryParameterArrayIsIgnored(): void
    {
        $uri = new Uri('/?app[]=x');
        $this->assertSame('', $uri->getApp());
    }

    public function testNestedPathIsNoApp(): void
    {
        $uri = new Uri('/app1/sub');
        $this->assertSame('', $uri->getApp());
        $this->assertSame('/app1/sub', $uri->getPath());
    }

    /**
     * @return array<string, array{array<string, string>, string|null}>
     */
    public static function serverProvider(): array
    {
        return [
            'http' => [['HTTP_HOST' => 'example.com'], 'http://example.com'],
            'https' => [['HTTP_HOST' => 'example.com', 'HTTPS' => 'on'], 'https://example.com'],
            'https off' => [['HTTP_HOST' => 'example.com', 'HTTPS' => 'off'], 'http://example.com'],
            'with port' => [['HTTP_HOST' => 'localhost:9080'], 'http://localhost:9080'],
            'behind proxy' => [
                ['HTTP_HOST' => 'example.com', 'HTTP_X_FORWARDED_PROTO' => 'https'],
                'https://example.com',
            ],
            'no host' => [[], null],
            'invalid host' => [['HTTP_HOST' => 'evil.com/"><script>'], null],
        ];
    }

    /**
     * @param array<string, string> $server
     */
    #[DataProvider('serverProvider')]
    public function testBaseUrlFromServer(array $server, ?string $expected): void
    {
        $this->assertSame($expected, Uri::baseUrlFromServer($server));
    }
}
