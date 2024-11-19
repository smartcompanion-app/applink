<?php

namespace SmartCompanion;

class AppLink
{
    const REGEX_IOS_USER_AGENT = '/iPhone|iPad|iPod/';
    const REGEX_ANDROID_USER_AGENT = '/Android/';

    public function tryRedirectToApp(array $appLinks, string $userAgentString): bool
    {
        $detectedOS = $this->detectMobileOS($userAgentString);
        if (isset($appLinks[$detectedOS])) {
            $this->redirect($appLinks[$detectedOS]);
            return true;
        }
        return false;
    }

    public function detectMobileOS(string $userAgentString)
    {
        if (preg_match(self::REGEX_IOS_USER_AGENT, $userAgentString)) {
            return 'ios';
        } elseif (preg_match(self::REGEX_ANDROID_USER_AGENT, $userAgentString)) {
            return 'android';
        } else {
            return 'other';
        }
    }

    public function redirect(string $link)
    {
        header("Location: $link", true, 302);
    }
}
