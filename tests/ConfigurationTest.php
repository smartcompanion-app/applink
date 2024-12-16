<?php

namespace SmartCompanion\Tests;

use PHPUnit\Framework\TestCase;
use SmartCompanion\Configuration;

class ConfigurationTest extends TestCase
{
    protected Configuration $configuration;

    public function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->configuration->setConfigurationDirectoryPath(__DIR__ . '/test_configuration_directory');
    }

    public function testReadAppConfiguration(): void
    {
        $configuration = $this->configuration->readAppConfiguration('myapp');
        $this->assertEquals('My App', $configuration['page']['title']);
    }

    public function testHasAppConfiguration(): void
    {
        $this->assertTrue($this->configuration->hasAppConfiguration('myapp'));
        $this->assertTrue($this->configuration->hasAppConfiguration('my-other_app123'));
        $this->assertFalse($this->configuration->hasAppConfiguration('badexample&%$'));
    }
}
