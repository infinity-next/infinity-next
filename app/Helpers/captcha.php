<?php

if (!function_exists('captcha'))
{
	function captcha($profile = 'default')
	{
		return app('captcha')->getAsHtml($profile);
	}
}
