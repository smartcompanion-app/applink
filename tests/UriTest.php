<?php

namespace SmartCompanion\Tests;

use PHPUnit\Framework\TestCase;
use SmartCompanion\Uri;

class UriTest extends TestCase
{
    public function testAppFromPath(): void
    {
        $uri = new Uri('/app1');
        $this->assertEquals('app1', $uri->getApp());
    }

    public function testAppFromQuery(): void
    {
        $uri = new Uri('/?app=app2');
        $this->assertEquals('app2', $uri->getApp());
    }

    public function testAppFromPathAndQuery(): void
    {
        $uri = new Uri('/app3?app=app4');
        $this->assertEquals('app3', $uri->getApp());
    }

    public function testIncorrectPath(): void
    {
        $uri = new Uri('/  %%&$%?x=y');
        $this->assertEquals('', $uri->getApp());
    }
}
