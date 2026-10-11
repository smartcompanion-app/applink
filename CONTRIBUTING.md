# Contributing

This guide covers developing, testing and releasing applink.

## Developing

### Local Setup

1. Fork and clone the repo.
1. Install PHP 8.4 or later and [Composer](https://getcomposer.org/).
1. Install the dependencies.

    ```shell
    composer install
    ```

1. Serve the example configuration on [http://localhost:8080/atlas-trails](http://localhost:8080/atlas-trails).

    ```shell
    composer serve
    ```

`composer serve` runs PHP's built-in web server with `bin/dev-router.php`. The router works the same way as the `Caddyfile` in the Docker image: static files come from `public/`, `/icons/*` comes from the storage directory, and everything else goes to `public/index.php`. To see a redirect instead of the fallback page, open the link with a phone or switch your browser's dev tools to a mobile device.

### Project layout

| Path | |
| --- | --- |
| `public/index.php` | Entry point. Hands the request to `Application` and sends the response. |
| `src/Application.php` | Maps a request to a response: redirect, fallback page, health check or error page. |
| `src/AppLink.php` | Detects the mobile OS from the user agent and picks the matching store link. |
| `src/Configuration.php`, `src/AppConfiguration.php` | Reads and validates `storage/configuration/<app>.yml`. |
| `src/Uri.php` | Extracts the app identifier from `/<app>` or `/?app=<app>`. |
| `src/*.html.php` | Templates for the fallback page and the error page, rendered by `src/View.php`. |
| `docker/` | `Caddyfile` and `php.ini` of the FrankenPHP based image. |
| `docs/example_storage/` | Example configuration, used by the README, `composer serve` and the smoke test. |

### Scripts

| Command | |
| --- | --- |
| `composer check` | Everything CI checks on PHP code: `lint`, `analyse` and `test`. |
| `composer test` | PHPUnit test suite. |
| `composer lint` | PSR-12 code style via PHP_CodeSniffer. `composer lint:fix` fixes what it can. |
| `composer analyse` | PHPStan on level `max`. |
| `composer serve` | Development server for `docs/example_storage`. |

## Testing

The unit tests in `tests/` cover the whole request handling. `ApplicationTest` calls `Application::handle()` with a set of `$_SERVER` variables and checks the response: status, headers and the rendered HTML. The configuration fixtures live in `tests/test_configuration_directory/`.

The Docker image has its own smoke test. `bin/smoke-test.sh` sends real HTTP requests to a running container and checks the redirects, the pages, the static files and the headers. CI runs it on every pull request.

```shell
docker compose up -d --build
bin/smoke-test.sh http://localhost:9080
docker compose down
```

## Releases

Releases are automated with [release-please](https://github.com/googleapis/release-please). It reads the [Conventional Commits](https://www.conventionalcommits.org/) on `main`, so **the commit message, or the PR title for a squash merge, decides the next version**:

| Prefix | Example | Release |
| --- | --- | --- |
| `fix:` | `fix: escape the app title` | patch |
| `feat:` | `feat: add og:description` | minor |
| `feat!:` or a `BREAKING CHANGE:` footer | `feat!: rename page.title` | major (minor while on 0.x) |
| `deps:` | `deps: bump symfony/yaml` | patch |
| `docs:`, `chore:`, `ci:`, `test:`, `refactor:` | `ci: cache composer` | none |

Every push to `main` updates a `chore: release` pull request, which collects the pending changes, bumps the version and updates `CHANGELOG.md`. Merging that pull request tags the release and creates the GitHub release. It also publishes the multi-platform (`linux/amd64`, `linux/arm64`) Docker image to `ghcr.io/smartcompanion-app/applink` and `stefanhuber332/applink`.

Never edit the version or `CHANGELOG.md` by hand. Both are generated.

> **Note**: Pull requests opened with the default `GITHUB_TOKEN` do not trigger workflows, so the release pull request would have no CI checks. To avoid that, add a `RELEASE_TOKEN` repository secret: a fine-grained personal access token scoped to this repository with *Contents: read and write* and *Pull requests: read and write*. The workflow falls back to `GITHUB_TOKEN` when the secret is absent.

Publishing to Docker Hub uses the `DOCKERHUB_USERNAME` repository variable and the `DOCKERHUB_TOKEN` secret. GHCR uses the workflow's own token.
