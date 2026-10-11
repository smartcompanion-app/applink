<?php

declare(strict_types=1);

namespace SmartCompanion;

class AppLink
{
    public const REGEX_IOS_USER_AGENT = '/iPhone|iPad|iPod/';
    public const REGEX_ANDROID_USER_AGENT = '/Android/';

    /**
     * Returns the app market link matching the device of the user agent, or null if there is none.
     *
     * iPads on iPadOS 13+ identify as a Mac and are not detected here; the fallback page
     * handles them on the client side.
     *
     * @param array<string, string> $appLinks
     */
    public function resolveLink(array $appLinks, string $userAgentString): ?string
    {
        return $appLinks[$this->detectMobileOS($userAgentString)] ?? null;
    }

    public function detectMobileOS(string $userAgentString): string
    {
        if (preg_match(self::REGEX_IOS_USER_AGENT, $userAgentString)) {
            return 'ios';
        } elseif (preg_match(self::REGEX_ANDROID_USER_AGENT, $userAgentString)) {
            return 'android';
        } else {
            return 'other';
        }
    }
}
