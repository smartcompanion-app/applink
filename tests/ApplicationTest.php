<?php

declare(strict_types=1);

namespace SmartCompanion\Tests;

use PHPUnit\Framework\TestCase;
use SmartCompanion\Application;
use SmartCompanion\Configuration;

class ApplicationTest extends TestCase
{
    protected Application $application;

    protected function setUp(): void
    {
        $configuration = new Configuration();
        $configuration->setConfigurationDirectoryPath(__DIR__ . '/test_configuration_directory');
        $this->application = new Application($configuration);
    }

    public function testRedirectsIPhoneToAppStore(): void
    {
        $response = $this->application->handle([
            'REQUEST_URI' => '/myapp',
            'HTTP_USER_AGENT' => AppLinkTest::UA_IPHONE,
        ]);

        $this->assertSame(302, $response->status);
        $this->assertSame('https://itunes.apple.com/us/app/myapp/id123456789', $response->headers['Location']);
        $this->assertSame('User-Agent', $response->headers['Vary']);
        $this->assertSame('no-store', $response->headers['Cache-Control']);
    }

    public function testRedirectsAndroidToGooglePlay(): void
    {
        $response = $this->application->handle([
            'REQUEST_URI' => '/?app=myapp',
            'HTTP_USER_AGENT' => AppLinkTest::UA_ANDROID_PHONE,
        ]);

        $this->assertSame(302, $response->status);
        $this->assertSame('https://play.google.com/store/apps/details?id=com.myapp', $response->headers['Location']);
    }

    public function testShowsFallbackPageOnDesktop(): void
    {
        $response = $this->application->handle([
            'REQUEST_URI' => '/myapp',
            'HTTP_USER_AGENT' => AppLinkTest::UA_WINDOWS,
            'HTTP_HOST' => 'localhost:9080',
        ]);

        $this->assertSame(200, $response->status);
        $this->assertSame('User-Agent', $response->headers['Vary']);
        $this->assertStringContainsString('<title>My App</title>', $response->body);
        $this->assertStringContainsString('content="http://localhost:9080/myapp"', $response->body);
        $this->assertStringContainsString('content="http://localhost:9080/icons/myapp.png"', $response->body);
        $this->assertStringContainsString('href="https://itunes.apple.com/us/app/myapp/id123456789"', $response->body);
        $this->assertStringContainsString('style="background: #f0f0f0"', $response->body);
    }

    public function testFallbackPageScriptMatchesContentSecurityPolicy(): void
    {
        $response = $this->application->handle(['REQUEST_URI' => '/myapp']);

        $csp = $response->headers['Content-Security-Policy'];

        if (preg_match("/script-src 'nonce-([^']+)'/", $csp, $match) !== 1) {
            $this->fail("Content-Security-Policy has no script nonce: $csp");
        }
        $this->assertStringContainsString('<script nonce="' . $match[1] . '">', $response->body);
        $this->assertStringContainsString("img-src 'self' https: data:", $csp);
        $this->assertSame('nosniff', $response->headers['X-Content-Type-Options']);
    }

    public function testShowsFallbackPageWithoutUserAgent(): void
    {
        $response = $this->application->handle(['REQUEST_URI' => '/myapp']);

        $this->assertSame(200, $response->status);
        $this->assertStringNotContainsString('og:url', $response->body);
    }

    public function testShowsFallbackPageForMissingPlatformLink(): void
    {
        $response = $this->application->handle([
            'REQUEST_URI' => '/minimal',
            'HTTP_USER_AGENT' => AppLinkTest::UA_IPHONE,
        ]);

        $this->assertSame(200, $response->status);
        $this->assertStringContainsString('google-play-badge.svg', $response->body);
        $this->assertStringNotContainsString('app-store-badge.svg', $response->body);
        $this->assertStringNotContainsString('<body style=', $response->body);
        $this->assertStringNotContainsString('app-icon', $response->body);
    }

    public function testFallbackPageEscapesConfiguration(): void
    {
        $response = $this->application->handle(['REQUEST_URI' => '/escaping', 'HTTP_HOST' => 'ignored.example.com']);

        $this->assertSame(200, $response->status);
        $this->assertStringNotContainsString('<script>alert', $response->body);
        $this->assertStringNotContainsString('"><script>', $response->body);
        $this->assertStringContainsString('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', $response->body);
        $this->assertStringContainsString('Tom &amp; Jerry&apos;s &quot;App&quot;', $response->body);
        $this->assertStringContainsString('href="https://apps.apple.com/app/id1?a=1&amp;b=2"', $response->body);
        $this->assertStringContainsString('content="https://applink.example.com/icons/my%20icon.png"', $response->body);
    }

    public function testUnknownAppReturnsNotFound(): void
    {
        $response = $this->application->handle(['REQUEST_URI' => '/does-not-exist']);

        $this->assertSame(404, $response->status);
        $this->assertStringContainsString('App not found', $response->body);
    }

    public function testRootReturnsNotFound(): void
    {
        $this->assertSame(404, $this->application->handle(['REQUEST_URI' => '/'])->status);
        $this->assertSame(404, $this->application->handle([])->status);
    }

    public function testInvalidConfigurationReturnsServerError(): void
    {
        $errorLog = ini_set('error_log', '/dev/null');
        try {
            $response = $this->application->handle(['REQUEST_URI' => '/broken-yaml']);
        } finally {
            ini_set('error_log', (string) $errorLog);
        }

        $this->assertSame(500, $response->status);
        $this->assertStringContainsString('Something went wrong', $response->body);
        $this->assertStringNotContainsString('unclosed', $response->body);
    }

    public function testHealthCheck(): void
    {
        $response = $this->application->handle(['REQUEST_URI' => '/healthz']);

        $this->assertSame(200, $response->status);
        $this->assertSame("ok\n", $response->body);
    }
}
