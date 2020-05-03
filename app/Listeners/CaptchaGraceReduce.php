<?php

namespace App\Listeners;

use Cache;
use Session;

class CaptchaGraceReduce extends Listener
{
    /**
     * Handle the event.
     *
     * @param Event $event
     */
    public function handle($event)
    {
         // burn dirty captchas
        if (is_hidden_service()) {
            Cache::forget("captcha.grace.{$session}");
        }
        
        if (!site_setting('captchaEnabled')) {
            return;
        }

        // If this setting is 0, grace is strictly time.
        if ((int) site_setting('captchaLifespanPosts') < 1) {
            return;
        }

        $session = Session::getId();
        $graceRemaining = Cache::decrement("captcha.grace.{$session}");

        if ($graceRemaining < 1) {
            Cache::forget("captcha.grace.{$session}");
        }
    }
}
