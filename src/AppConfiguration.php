<?php

declare(strict_types=1);

namespace SmartCompanion;

/**
 * Validated configuration of a single app, read from `storage/configuration/<identifier>.yml`.
 */
final readonly class AppConfiguration
{
    public const PLATFORMS = ['ios', 'android'];
    public const LINK_SCHEMES = ['https', 'http', 'itms-apps', 'market'];

    /**
     * @param array<'ios'|'android', string> $links
     */
    public function __construct(
        public string $identifier,
        public array $links,
        public string $title,
        public ?string $description = null,
        public ?string $baseUrl = null,
        public ?string $backgroundColor = null,
        public ?string $appIcon = null,
    ) {
    }

    /**
     * Creates the configuration from the parsed YAML file and validates it.
     *
     * @throws ConfigurationException
     */
    public static function fromArray(string $identifier, mixed $data): self
    {
        if (!is_array($data)) {
            throw new ConfigurationException("Configuration of app \"$identifier\" must be a YAML mapping");
        }

        $links = [];
        $rawLinks = $data['links'] ?? null;
        if (!is_array($rawLinks)) {
            throw new ConfigurationException("Configuration of app \"$identifier\" requires a \"links\" mapping");
        }
        foreach (self::PLATFORMS as $platform) {
            if (!isset($rawLinks[$platform])) {
                continue;
            }
            $link = $rawLinks[$platform];
            if (!is_string($link) || !in_array(parse_url($link, PHP_URL_SCHEME), self::LINK_SCHEMES, true)) {
                throw new ConfigurationException(
                    "Link \"links.$platform\" of app \"$identifier\" must be an absolute URL using one of: "
                    . implode(', ', self::LINK_SCHEMES)
                );
            }
            $links[$platform] = $link;
        }
        if ($links === []) {
            throw new ConfigurationException(
                "Configuration of app \"$identifier\" requires at least one of \"links.ios\" or \"links.android\""
            );
        }

        $page = $data['page'] ?? [];
        if (!is_array($page)) {
            throw new ConfigurationException("Option \"page\" of app \"$identifier\" must be a mapping");
        }
        $title = self::optionalString($identifier, $page, 'title');
        if ($title === null) {
            throw new ConfigurationException("Configuration of app \"$identifier\" requires a \"page.title\"");
        }
        $baseUrl = self::optionalString($identifier, $page, 'base-url');

        return new self(
            identifier: $identifier,
            links: $links,
            title: $title,
            description: self::optionalString($identifier, $page, 'description'),
            baseUrl: $baseUrl !== null ? rtrim($baseUrl, '/') : null,
            backgroundColor: self::optionalString($identifier, $page, 'background-color'),
            appIcon: self::optionalString($identifier, $page, 'app-icon'),
        );
    }

    /**
     * @param array<mixed> $page
     * @throws ConfigurationException
     */
    private static function optionalString(string $identifier, array $page, string $key): ?string
    {
        $value = $page[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }
        if (!is_string($value)) {
            throw new ConfigurationException("Option \"page.$key\" of app \"$identifier\" must be a string");
        }
        return $value;
    }
}
