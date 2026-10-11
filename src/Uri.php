<?php

declare(strict_types=1);

namespace SmartCompanion;

class Uri
{
    public const REGEX_APP_PATH = '/^\/([A-Za-z0-9\-_]+)$/';
    public const REGEX_APP_QUERY = '/^[A-Za-z0-9\-_]+$/';

    /**
     * @var array{path?: string, query?: string}
     */
    protected array $uri = [];

    public function __construct(?string $uri = null)
    {
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->uri = $this->prepareUri($uri ?? (is_string($requestUri) ? $requestUri : '/'));
    }

    /**
     * @return array{path?: string, query?: string}
     */
    public function prepareUri(string $uri): array
    {
        $output = [];
        $result = parse_url($uri);

        if ($result === false) {
            return $output;
        }
        if (isset($result['path'])) {
            $output['path'] = $result['path'];
        }
        if (isset($result['query'])) {
            $output['query'] = $result['query'];
        }

        return $output;
    }

    public function getPath(): string
    {
        return $this->uri['path'] ?? '/';
    }

    public function getApp(): string
    {
        if (isset($this->uri['path']) && preg_match(self::REGEX_APP_PATH, $this->uri['path'], $match)) {
            return $match[1];
        } elseif (isset($this->uri['query'])) {
            parse_str($this->uri['query'], $query);
            if (isset($query['app']) && is_string($query['app']) && preg_match(self::REGEX_APP_QUERY, $query['app'])) {
                return $query['app'];
            }
        }

        return '';
    }

    /**
     * Derives the public base URL (scheme and host) of the service from the server variables,
     * honouring `X-Forwarded-Proto` set by a TLS terminating reverse proxy.
     *
     * @param array<mixed> $server
     */
    public static function baseUrlFromServer(array $server): ?string
    {
        $host = $server['HTTP_HOST'] ?? null;
        if (!is_string($host) || !preg_match('/^[A-Za-z0-9.\-]+(:\d+)?$/', $host)) {
            return null;
        }

        $https = ($server['HTTPS'] ?? '') !== '' && ($server['HTTPS'] ?? '') !== 'off';
        $forwardedProto = $server['HTTP_X_FORWARDED_PROTO'] ?? null;
        if (is_string($forwardedProto) && $forwardedProto !== '') {
            $https = strtolower(trim(explode(',', $forwardedProto)[0])) === 'https';
        }

        return ($https ? 'https' : 'http') . '://' . $host;
    }
}
