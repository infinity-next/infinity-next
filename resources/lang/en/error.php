<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Errors
    |--------------------------------------------------------------------------
    |
    | Whole page errors, or container errors.
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
    '410' => [
        'title' => "Gone",
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

    'js'  => [
        'desc'  => "Our system uses JavaScript to avoid dealing directly with credit card information.<br />If you'd like to donate, please enable it so your transaction can be securely handled.",
        'title' => "JavaScript is Required",
    ],

    'ssl' => [
        'desc'  => "This form deals in private information. Please use HTTPS.",
        'title' => "SSL is Required",
    ],

    'account' => [
        'desc'  => "Braintree's API requires us to handle an identifier for the duration of your session. To donate with a card, please register first.",
        'title' => "Account required",
    ],
];
