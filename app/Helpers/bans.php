<?php

if (!function_exists('ip_less')) {
    function ip_less($ip)
    {
        return substr(
            md5(env('APP_KEY').$ip),
            0,
            strpos($ip, ':') === false ? 12 : 24
        );
    }
}
