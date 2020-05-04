<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Errors
    |--------------------------------------------------------------------------
    |
    | Whole page errors
    |
    */

    '400' => [
        'title' => "Bad Request",
    ],
    '403' => [
        'title' => "Forbidden",
    ],
    '403_tor_clearnet' => [
        'title' => "Tor Connection on Clearnet",
        'desc'  => "Use the Hidden Service",
    ],
    '404' => [
        'title' => "Not Found",
    ],
    '405' => [
        'title' => "Method Not Allowed",
    ],
    '410' => [
        'title' => "Gone",
    ],
    '418' => [
        'title' => "I'm a teapot",
    ],
    '429' => [
        'title' => "Slow Down",
    ],
    '451' => [
        'title' => "Unavailable For Legal Reasons",
    ],
    '500' => [
        'title' => "Internal Server Error",
    ],
    '500_config' => [
        'title' => "Misconfiguration",
    ],
    '503' => [
        'title' => "Maintenance",
    ],
];
