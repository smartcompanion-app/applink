<?php

namespace SmartCompanion;

class Uri
{
    public const REGEX_APP_PATH = '/^\/([A-Za-z0-9\-_]+)$/';
    public const REGEX_APP_QUERY = '/^[A-Za-z0-9\-_]+$/';

    /**
     * @var array<string, string>
     */
    protected array $uri = [];

    public function __construct(string $uri = null)
    {
        $this->uri = $this->prepareUri(is_null($uri) ? $_SERVER['REQUEST_URI'] : $uri);
    }

    /**
     * @return array<string, string>
     */
    public function prepareUri(string $uri): array
    {
        $output = [];
        $result = parse_url($uri);

        if (isset($result['path'])) {
            $output['path'] = $result['path'];
        }
        if (isset($result['query'])) {
            $output['query'] = $result['query'];
        }

        return $output;
    }

    public function getApp(): string
    {
        if (isset($this->uri['path']) && preg_match(self::REGEX_APP_PATH, $this->uri['path'], $match)) {
            return $match[1];
        } elseif (isset($this->uri['query'])) {
            parse_str($this->uri['query'], $query);
            if (isset($query['app']) && preg_match(self::REGEX_APP_QUERY, $query['app'])) {
                return $query['app'];
            }
        }

        return '';
    }
}
