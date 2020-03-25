<?php

if (!function_exists('site_setting')) {
    function site_setting($site_setting)
    {
        return app('settings')->get($site_setting);
    }
}


if (!function_exists('user')) {
    function user()
    {
        return auth()->user();
    }
}
