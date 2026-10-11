## What changed

<!-- What does this do, and why? Link any issue it closes. -->

## Checklist

- [ ] `composer check` passes (code style, PHPStan, tests)
- [ ] The PR title follows [Conventional Commits](https://www.conventionalcommits.org/) (`feat:`, `fix:`, ...) — it becomes the changelog entry, see [CONTRIBUTING.md](../CONTRIBUTING.md#releases)
- [ ] Existing app configuration files keep working, or the change is marked as breaking (`feat!:`)
- [ ] New configuration options are documented in the README
- [ ] `bin/smoke-test.sh` passes against the Docker image, if the Dockerfile or Caddyfile changed
