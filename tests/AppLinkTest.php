<?php

declare(strict_types=1);

namespace SmartCompanion\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartCompanion\AppLink;

class AppLinkTest extends TestCase
{
    // phpcs:disable Generic.Files.LineLength
    public const UA_IPHONE = 'Mozilla/5.0 (iPhone14,6; U; CPU iPhone OS 15_4 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Mobile/19E241 Safari/602.1';
    public const UA_IPAD_LEGACY = 'Mozilla/5.0 (iPad; CPU OS 12_2 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148';
    public const UA_IPAD_DESKTOP_MODE = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';
    public const UA_ANDROID_PHONE = 'Mozilla/5.0 (Linux; Android 13; SM-S901B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36';
    public const UA_ANDROID_TABLET = 'Mozilla/5.0 (Linux; Android 14; SM-X710) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
    public const UA_WINDOWS = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246';
    // phpcs:enable

    protected AppLink $appLink;

    protected function setUp(): void
    {
        $this->appLink = new AppLink();
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function userAgentProvider(): array
    {
        return [
            'iPhone' => [self::UA_IPHONE, 'ios'],
            'iPad (iOS 12)' => [self::UA_IPAD_LEGACY, 'ios'],
            'iPad (iPadOS 13+, detected client side)' => [self::UA_IPAD_DESKTOP_MODE, 'other'],
            'Android phone' => [self::UA_ANDROID_PHONE, 'android'],
            'Android tablet' => [self::UA_ANDROID_TABLET, 'android'],
            'Windows' => [self::UA_WINDOWS, 'other'],
            'empty' => ['', 'other'],
        ];
    }

    #[DataProvider('userAgentProvider')]
    public function testDetectMobileOS(string $userAgent, string $expectedOS): void
    {
        $this->assertSame($expectedOS, $this->appLink->detectMobileOS($userAgent));
    }

    public function testResolveLink(): void
    {
        $links = [
            'ios' => 'https://apps.apple.com/app/id1',
            'android' => 'https://play.google.com/store/apps/details?id=a',
        ];

        $this->assertSame($links['ios'], $this->appLink->resolveLink($links, self::UA_IPHONE));
        $this->assertSame($links['android'], $this->appLink->resolveLink($links, self::UA_ANDROID_PHONE));
        $this->assertNull($this->appLink->resolveLink($links, self::UA_WINDOWS));
    }

    public function testResolveLinkWithoutLinkForPlatform(): void
    {
        $links = ['android' => 'https://play.google.com/store/apps/details?id=a'];

        $this->assertNull($this->appLink->resolveLink($links, self::UA_IPHONE));
    }
}
