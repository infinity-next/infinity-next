<?php

namespace App\Listeners;

use Cache;
use Session;

class CaptchaGraceSet extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
        if (!site_setting('captchaEnabled')) {
            return;
        }

        if (is_hidden_service()) {
            return;
        }

        $session = Session::getId();
        $gracePosts = (int) site_setting('captchaLifespanPosts');
        $graceMinutes = (int) site_setting('captchaLifespanTime');

        Cache::put("captcha.grace.{$session}", $gracePosts, now()->addMinutes($graceMinutes));
    }
}
