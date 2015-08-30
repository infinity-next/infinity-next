<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Allow
	|--------------------------------------------------------------------------
	|
	| Determines if we allow Tor traffic. If set to FALSE, all Tor traffic
	| will only receive a 403 Forbidden error.
	| Unless you have a really good reason, you should permit Tor traffic.
	| It is possible to disable certain privileges (like posting images)
	| specifically for Unaccountable (Tor) users.
	|
	| Supported: true, false
	|
	*/
	'allow'  => true,
	
	/*
	|--------------------------------------------------------------------------
	| Request
	|--------------------------------------------------------------------------
	|
	| This config setting indicates if the current request is from a Tor service.
	| This should not be changed manually.
	| The definition is in /public/index-tor.php
	|
	| Supported: true, false
	|
	*/
	'request' => defined('INFINITY_NEXT_TOR') && INFINITY_NEXT_TOR,
	
	'url'     => env('APP_URL_HS'),
];