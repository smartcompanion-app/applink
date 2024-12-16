<?php

namespace SmartCompanion;

class AppLink
{
    public const REGEX_IOS_USER_AGENT = '/iPhone|iPad|iPod/';
    public const REGEX_ANDROID_USER_AGENT = '/Android/';

    /**
     * @param array<string, string> $appLinks
     * @param string $userAgentString
     */
    public function tryRedirectToApp(array $appLinks, string $userAgentString): bool
    {
        $detectedOS = $this->detectMobileOS($userAgentString);
        if (isset($appLinks[$detectedOS])) {
            $this->redirect($appLinks[$detectedOS]);
            return true;
        }
        return false;
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

    public function redirect(string $link): void
    {
        header("Location: $link", true, 302);
    }
}
