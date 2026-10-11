<?php

declare(strict_types=1);

namespace SmartCompanion\Tests;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use SmartCompanion\AppConfiguration;
use SmartCompanion\Configuration;
use SmartCompanion\ConfigurationException;

class ConfigurationTest extends TestCase
{
    protected Configuration $configuration;

    protected function setUp(): void
    {
        $this->configuration = new Configuration();
        $this->configuration->setConfigurationDirectoryPath(__DIR__ . '/test_configuration_directory');
    }

    protected function tearDown(): void
    {
        putenv('APPLINK_STORAGE');
    }

    public function testReadAppConfiguration(): void
    {
        $configuration = $this->configuration->readAppConfiguration('myapp');

        $this->assertSame('myapp', $configuration->identifier);
        $this->assertSame('My App', $configuration->title);
        $this->assertSame('#f0f0f0', $configuration->backgroundColor);
        $this->assertSame('myapp.png', $configuration->appIcon);
        $this->assertSame('https://play.google.com/store/apps/details?id=com.myapp', $configuration->links['android']);
        $this->assertNull($configuration->baseUrl);
        $this->assertNull($configuration->description);
    }

    public function testReadMinimalAppConfiguration(): void
    {
        $configuration = $this->configuration->readAppConfiguration('minimal');

        $this->assertSame(
            ['android' => 'https://play.google.com/store/apps/details?id=com.minimal'],
            $configuration->links
        );
        $this->assertNull($configuration->backgroundColor);
        $this->assertNull($configuration->appIcon);
    }

    public function testBaseUrlTrailingSlashIsRemoved(): void
    {
        $this->assertSame(
            'https://applink.example.com',
            $this->configuration->readAppConfiguration('escaping')->baseUrl
        );
    }

    public function testHasAppConfiguration(): void
    {
        $this->assertTrue($this->configuration->hasAppConfiguration('myapp'));
        $this->assertTrue($this->configuration->hasAppConfiguration('my-other_app123'));
        $this->assertFalse($this->configuration->hasAppConfiguration('badexample&%$'));
        $this->assertFalse($this->configuration->hasAppConfiguration('does-not-exist'));
        $this->assertFalse($this->configuration->hasAppConfiguration(''));
        $this->assertFalse($this->configuration->hasAppConfiguration('../ConfigurationTest'));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function invalidConfigurationProvider(): array
    {
        return [
            'empty file' => ['my-other_app123', 'must be a YAML mapping'],
            'unsafe link scheme' => ['invalid-link', 'must be an absolute URL'],
            'missing title' => ['no-title', 'requires a "page.title"'],
            'yaml syntax error' => ['broken-yaml', 'Error reading configuration file'],
            'unknown app' => ['does-not-exist', 'No configuration found'],
            'path traversal' => ['../ConfigurationTest', 'No configuration found'],
        ];
    }

    #[DataProvider('invalidConfigurationProvider')]
    public function testInvalidConfigurationThrows(string $appIdentifier, string $expectedMessage): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage($expectedMessage);

        $this->configuration->readAppConfiguration($appIdentifier);
    }

    public function testRequiresAtLeastOneLink(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('requires at least one of');

        AppConfiguration::fromArray('app', [
            'links' => ['windows' => 'https://example.com'],
            'page' => ['title' => 'x'],
        ]);
    }

    public function testStoragePathFromConstructor(): void
    {
        $configuration = new Configuration(__DIR__ . '/../docs/example_storage');

        $this->assertTrue($configuration->hasAppConfiguration('atlas-trails'));
    }

    public function testStoragePathFromEnvironment(): void
    {
        putenv('APPLINK_STORAGE=' . __DIR__ . '/../docs/example_storage');

        $this->assertSame(__DIR__ . '/../docs/example_storage', Configuration::defaultStoragePath());
        $this->assertTrue((new Configuration())->hasAppConfiguration('atlas-trails'));
    }

    public function testExampleConfigurationIsValid(): void
    {
        $configuration = (new Configuration(__DIR__ . '/../docs/example_storage'))
            ->readAppConfiguration('atlas-trails');

        $this->assertSame('Atlas Trails', $configuration->title);
    }
}
