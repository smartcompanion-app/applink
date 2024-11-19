<?php

namespace SmartCompanion;

use Symfony\Component\Yaml\Yaml;

class Configuration
{
    const REGEX_APP_IDENTIFIER = '/^[A-Za-z0-9\-_]+$/';

    protected $configuationDirectoryPath = __DIR__ . "/../storage/configuration";

    public function readAppIdentifiers()
    {
        $appIdentifiers = [];
        $files = scandir($this->configuationDirectoryPath);
        foreach ($files as $file) {
            if (preg_match('/^([A-Za-z0-9\-_]+)\.yml$/', $file, $matches)) {
                $appIdentifiers[] = $matches[1];
            }
        }
        return $appIdentifiers;
    }

    public function hasAppConfiguration(string $appIdentifier)
    {        
        if (!preg_match(self::REGEX_APP_IDENTIFIER, $appIdentifier)) {
            return false;
        }       
        $file = $this->configuationDirectoryPath . '/' . $appIdentifier . '.yml';
        return file_exists($file);
    }

    public function readAppConfiguration(string $appIdentifier)
    {
        $file = $this->configuationDirectoryPath . '/' . $appIdentifier . '.yml';
        
        try {
            return Yaml::parseFile($file);
        } catch (\Exception $e) {
            throw new \Exception("Error reading configuration file for app identifier: $appIdentifier, details: " . $e->getMessage());
        }
    }

    public function __set($name, $value)
    {
        if ($name === 'configuationDirectoryPath') {
            $this->configuationDirectoryPath = $value;
        }
    }
}
