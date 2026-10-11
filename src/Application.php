<?php

declare(strict_types=1);

namespace SmartCompanion;

/**
 * Maps a request to a response: redirect to the app market, fallback page, health check or error page.
 */
class Application
{
    public const HEALTH_CHECK_PATH = '/healthz';

    public function __construct(
        protected Configuration $configuration = new Configuration(),
        protected AppLink $appLink = new AppLink(),
        protected View $view = new View(),
    ) {
    }

    /**
     * @param array<mixed> $server the `$_SERVER` variables of the request
     */
    public function handle(array $server): Response
    {
        $requestUri = $server['REQUEST_URI'] ?? '/';
        $userAgent = $server['HTTP_USER_AGENT'] ?? '';
        $uri = new Uri(is_string($requestUri) ? $requestUri : '/');

        if ($uri->getPath() === self::HEALTH_CHECK_PATH) {
            return new Response(
                200,
                ['Content-Type' => 'text/plain; charset=UTF-8', 'Cache-Control' => 'no-store'],
                "ok\n"
            );
        }

        try {
            $appIdentifier = $uri->getApp();
            if (!$this->configuration->hasAppConfiguration($appIdentifier)) {
                return $this->errorPage(404, 'App not found', 'There is no app available under this link.');
            }
            $app = $this->configuration->readAppConfiguration($appIdentifier);

            // The same URL answers differently per device, so it must not be cached across user agents.
            $headers = ['Vary' => 'User-Agent', 'Cache-Control' => 'no-store'];

            $link = $this->appLink->resolveLink($app->links, is_string($userAgent) ? $userAgent : '');
            if ($link !== null) {
                return new Response(302, ['Location' => $link] + $headers);
            }

            $nonce = base64_encode(random_bytes(16));
            return $this->htmlResponse(200, $this->view->render('template', [
                'app' => $app,
                'baseUrl' => $app->baseUrl ?? Uri::baseUrlFromServer($server),
                'nonce' => $nonce,
            ]), $nonce, $headers);
        } catch (\Throwable $e) {
            error_log('[applink] ' . $e->getMessage());
            return $this->errorPage(500, 'Something went wrong', 'This link is not available at the moment.');
        }
    }

    protected function errorPage(int $status, string $title, string $message): Response
    {
        return $this->htmlResponse($status, $this->view->render('error', [
            'status' => $status,
            'title' => $title,
            'message' => $message,
        ]));
    }

    /**
     * @param array<string, string> $headers
     */
    protected function htmlResponse(
        int $status,
        string $body,
        ?string $scriptNonce = null,
        array $headers = []
    ): Response {
        $scriptSource = $scriptNonce !== null ? "'nonce-$scriptNonce'" : "'none'";

        return new Response($status, $headers + [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self'; style-src 'self' 'unsafe-inline'; "
                . "script-src $scriptSource; base-uri 'none'; form-action 'none'; frame-ancestors 'none'",
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ], $body);
    }
}
