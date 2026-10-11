<?php

declare(strict_types=1);

namespace SmartCompanion;

class View
{
    public function __construct(protected string $templateDirectoryPath = __DIR__)
    {
    }

    /**
     * Renders `<template>.html.php`, exposing `$data` as variables and the escape helper as `$e`.
     *
     * @param array<string, mixed> $data
     */
    public function render(string $template, array $data = []): string
    {
        $e = self::escape(...);
        extract($data, EXTR_SKIP);

        ob_start();
        try {
            include $this->templateDirectoryPath . '/' . $template . '.html.php';
        } catch (\Throwable $exception) {
            ob_end_clean();
            throw $exception;
        }
        return (string) ob_get_clean();
    }

    public static function escape(?string $value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
