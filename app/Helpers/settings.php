<?php

if (!function_exists('site_setting'))
{
	function site_setting($site_setting)
	{
		return App\SiteSetting::getValue($site_setting);
	}
}