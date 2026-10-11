#!/usr/bin/env bash
#
# Smoke test against a running applink instance serving docs/example_storage.
#
#   docker compose up -d --build && bin/smoke-test.sh http://localhost:9080
#
set -euo pipefail

BASE_URL="${1:-http://localhost:9080}"
IPHONE="Mozilla/5.0 (iPhone; CPU iPhone OS 18_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148"
ANDROID="Mozilla/5.0 (Linux; Android 15; Pixel 9) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Mobile Safari/537.36"
failures=0

# expect <description> <expected "status location"> <curl args...>
expect() {
    local description="$1" expected="$2"
    shift 2
    local actual
    actual="$(curl -s -o /dev/null -w '%{http_code} %{redirect_url}' "$@")"
    actual="${actual% }"
    if [[ "$actual" == "$expected" ]]; then
        echo "ok   $description"
    else
        echo "FAIL $description: expected '$expected', got '$actual'"
        failures=$((failures + 1))
    fi
}

expect "fallback page on desktop" "200" "$BASE_URL/atlas-trails"
expect "fallback page without user agent" "200" -H "User-Agent:" "$BASE_URL/atlas-trails"
expect "fallback page via query parameter" "200" "$BASE_URL/?app=atlas-trails"
expect "iPhone redirects to the App Store" \
    "302 https://apps.apple.com/app/atlas-trails/id6450123456" -A "$IPHONE" "$BASE_URL/atlas-trails"
expect "Android redirects to Google Play" \
    "302 https://play.google.com/store/apps/details?id=app.atlastrails" -A "$ANDROID" "$BASE_URL/atlas-trails"
expect "unknown app" "404" "$BASE_URL/does-not-exist"
expect "root" "404" "$BASE_URL/"
expect "health check" "200" "$BASE_URL/healthz"
expect "stylesheet" "200" "$BASE_URL/style.css"
expect "store badge" "200" "$BASE_URL/assets/app-store-badge.svg"
expect "app icon from storage" "200" "$BASE_URL/icons/atlas-trails.png"
expect "no configuration files through icons" "404" --path-as-is "$BASE_URL/icons/../configuration/atlas-trails.yml"

if ! curl -s -I -A "$IPHONE" "$BASE_URL/atlas-trails" | grep -qi '^vary: user-agent'; then
    echo "FAIL redirect is missing 'Vary: User-Agent'"
    failures=$((failures + 1))
else
    echo "ok   redirect varies by user agent"
fi

if [[ $failures -gt 0 ]]; then
    echo "$failures check(s) failed"
    exit 1
fi
echo "All checks passed"
