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
        $this->configuration->configuationDirectoryPath = __DIR__ . '/test_configuration_directory';
    }

    public function testReadAppIdentifiersFromTestConfigurationDirectory()
    {
        $this->assertEquals(['my-other_app123', 'myapp'], $this->configuration->readAppIdentifiers());
    }

    public function testReadAppConfiguration()
    {
        $configuration = $this->configuration->readAppConfiguration('myapp');
        $this->assertEquals('My App', $configuration['page']['title']);
    }

    public function testHasAppConfiguration()
    {
        $this->assertTrue($this->configuration->hasAppConfiguration('my-other_app123'));
        $this->assertFalse($this->configuration->hasAppConfiguration('badexample&%$'));
    }
}
