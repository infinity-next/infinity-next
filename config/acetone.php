<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Varnish Server Configuration
    |--------------------------------------------------------------------------
    |
    |   Connection information for the Varnish server you want to connect to.
    |
    |   This should probably be the FQDN for your Varnish server, as its used for creating
    |   the Hostname of the PURGE request, e.g. example.com:80
    |
    */

    'server' => array(
        "address" =>        "your-varnish.local",
    ),

    /*
    |--------------------------------------------------------------------------
    | Force Bad Response Exceptions
    |--------------------------------------------------------------------------
    |
    |   By default Acetone will allow exceptions to bubble through if debug mode is on (for local development).
    |   However in production, they will be caught and handled. This will stop things like a purge request returning a
    |   404 from halting the execution of your script.
    |
    |   'auto' => will show or hide depending on environment
    |   false => will always hide exceptions
    |   true => will always show exceptions
    |
    |   Supported: "auto", true, false
    */

    'force_exceptions' =>   "auto",

    /*
    |--------------------------------------------------------------------------
    | Ban X-Headers
    |--------------------------------------------------------------------------
    |
    |   The Header names to be used when requesting a ban for a URL.
    |
    */

    'ban_url_header' =>      'x-ban-url',
);
