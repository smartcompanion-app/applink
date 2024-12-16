<?php

namespace SmartCompanion\Tests;

use PHPUnit\Framework\TestCase;
use SmartCompanion\AppLink;

class AppLinkTest extends TestCase
{
    protected AppLink $appLink;

    public function setUp(): void
    {
        $this->appLink = new AppLink();
    }

    public function testDetectMobileOS(): void
    {
        $this->assertEquals("ios", $this->appLink->detectMobileOS("Mozilla/5.0 (iPhone14,6; U; CPU iPhone OS 15_4 like Mac OS X) AppleWebKit/602.1.50 (KHTML, like Gecko) Version/10.0 Mobile/19E241 Safari/602.1"));
        $this->assertEquals("android", $this->appLink->detectMobileOS("Mozilla/5.0 (Linux; Android 13; SM-S901B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Mobile Safari/537.36"));
        $this->assertEquals("other", $this->appLink->detectMobileOS("Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.135 Safari/537.36 Edge/12.246"));
    }
}
