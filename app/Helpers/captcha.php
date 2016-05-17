<?php

use \InfinityNext\LaravelCaptcha\Captcha;

if (!function_exists('captcha'))
{
	function captcha($profile = 'default')
	{
		$captcha = Captcha::findWithSession();

		if ($captcha instanceof Captcha)
		{
			return $captcha->getAsHtml($profile);
		}

		return app('captcha')->getAsHtml($profile);
	}
}
