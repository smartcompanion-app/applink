<?php

declare(strict_types=1);

namespace SmartCompanion;

use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class Configuration
{
    public const REGEX_APP_IDENTIFIER = '/^[A-Za-z0-9\-_]+$/';

    protected string $configurationDirectoryPath;

    /**
     * @param string|null $storagePath directory containing `configuration/` and `icons/`,
     *                                 defaults to the `APPLINK_STORAGE` environment variable or `./storage`
     */
    public function __construct(?string $storagePath = null)
    {
        $this->configurationDirectoryPath = rtrim($storagePath ?? self::defaultStoragePath(), '/') . '/configuration';
    }

    public static function defaultStoragePath(): string
    {
        $path = getenv('APPLINK_STORAGE');
        return is_string($path) && $path !== '' ? $path : dirname(__DIR__) . '/storage';
    }

    public function hasAppConfiguration(string $appIdentifier): bool
    {
        if (!preg_match(self::REGEX_APP_IDENTIFIER, $appIdentifier)) {
            return false;
        }
        return is_file($this->getConfigurationFilePath($appIdentifier));
    }

    /**
     * @throws ConfigurationException
     */
    public function readAppConfiguration(string $appIdentifier): AppConfiguration
    {
        if (!$this->hasAppConfiguration($appIdentifier)) {
            throw new ConfigurationException("No configuration found for app \"$appIdentifier\"");
        }

        try {
            $data = Yaml::parseFile($this->getConfigurationFilePath($appIdentifier));
        } catch (ParseException $e) {
            throw new ConfigurationException(
                "Error reading configuration file for app \"$appIdentifier\": " . $e->getMessage(),
                previous: $e
            );
        }

        return AppConfiguration::fromArray($appIdentifier, $data);
    }

    public function setConfigurationDirectoryPath(string $path): void
    {
        $this->configurationDirectoryPath = rtrim($path, '/');
    }

    protected function getConfigurationFilePath(string $appIdentifier): string
    {
        return $this->configurationDirectoryPath . '/' . $appIdentifier . '.yml';
    }
}
