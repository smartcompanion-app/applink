<?php

namespace SmartCompanion;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
    public const REGEX_APP_IDENTIFIER = '/^[A-Za-z0-9\-_]+$/';

    protected string $configuationDirectoryPath = __DIR__ . "/../storage/configuration";

    public function hasAppConfiguration(string $appIdentifier): bool
    {
        if (!preg_match(self::REGEX_APP_IDENTIFIER, $appIdentifier)) {
            return false;
        }
        $file = $this->configuationDirectoryPath . '/' . $appIdentifier . '.yml';
        return file_exists($file);
    }

    public function readAppConfiguration(string $appIdentifier): mixed
    {
        $file = $this->configuationDirectoryPath . '/' . $appIdentifier . '.yml';

        try {
            return Yaml::parseFile($file);
        } catch (\Exception $e) {
            throw new \Exception(
                "Error reading configuration file for app identifier: $appIdentifier, details: " . $e->getMessage()
            );
        }
    }

    public function setConfigurationDirectoryPath(string $path): void
    {
        $this->configuationDirectoryPath = $path;
    }
}
