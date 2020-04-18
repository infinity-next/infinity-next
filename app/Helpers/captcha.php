<?php

use InfinityNext\LaravelCaptcha\CaptchaChallenge;

if (!function_exists('captcha')) {
    function captcha($profile = 'default')
    {
        return (new CaptchaChallenge)->toHtml();
    }
}
