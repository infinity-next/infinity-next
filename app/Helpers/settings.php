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
        $user = auth()->user();

        if (is_null($user)) {
            $user = new \App\Auth\AnonymousUser;
            auth()->setUser($user);
        }

        return $user;
    }
}
