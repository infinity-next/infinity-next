<?php

use \InfinityNext\LaravelCaptcha\Captcha;

if (!function_exists('captcha')) {
    function captcha($profile = 'default')
    {
        $captcha = Captcha::findWithIP();

        if ($captcha instanceof Captcha) {
            return $captcha->getAsHtml($profile);
        }

        return app('captcha')->getAsHtml($profile);
    }
}
