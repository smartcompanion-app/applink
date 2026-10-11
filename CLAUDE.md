# CLAUDE.md

PHP microservice that redirects a single link to the App Store or Google Play based on the user agent, with a fallback page for everything else. Request handling in `src/Application.php`, entry point `public/index.php`, shipped as a FrankenPHP Docker image (`Dockerfile`, `docker/Caddyfile`).

See [CONTRIBUTING.md](CONTRIBUTING.md) for setup, scripts and the release flow — this file only covers things that are not obvious from the code.

## Constraints

**The configuration format is a public contract.** Users mount their own `configuration/*.yml` files, so existing keys (`links.ios`, `links.android`, `page.title`, `page.base-url`, `page.background-color`, `page.app-icon`) must keep working. New keys must be optional. `AppConfiguration::fromArray()` is the single place that validates them.

**So is the container layout.** The storage volume is mounted at `/applink/storage`, the container listens on port 80, and `/healthz` is reserved for the health check, so an app with that identifier cannot be served.

**Releases go through release-please.** Never bump `.release-please-manifest.json` or edit `CHANGELOG.md` by hand. Commit messages and squash-merged PR titles must be Conventional Commits, since they decide the version.

**`composer check` must stay green.** It runs PHPStan on level `max` and PSR-12. Fix the types rather than adding ignores or a baseline.

## Gotchas

- **Relative asset paths in the templates are deliberate.** `./style.css` and `./icons/...` keep working when applink is served under a sub-path behind a reverse proxy. Don't make them absolute.
- **iPadOS 13+ sends a desktop Safari user agent**, so the server cannot detect it. The fallback page has a small inline script (allowed by the CSP nonce in `Application::htmlResponse()`) that redirects touch-enabled "Macs" to the iOS link.
- **Every app response sends `Vary: User-Agent` and `Cache-Control: no-store`.** The same URL answers differently per device, so a CDN that cached it would send everyone to the same store.
- **Icons are served by Caddy, not PHP.** `docker/Caddyfile` maps `/icons/*` to the storage directory, and `bin/dev-router.php` copies that for `composer serve`. The two must stay in sync.
- **`docs/example_storage` is used by tests.** `ConfigurationTest` and `bin/smoke-test.sh` assert against its values, so update them together.
- **Base images are pinned to exact versions** so dependabot can bump them. A `deps:` commit from dependabot produces a patch release, which is how the published image picks up PHP and FrankenPHP security fixes.
