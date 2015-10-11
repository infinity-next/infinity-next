<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Third Party Services
	|--------------------------------------------------------------------------
	|
	| This file is for storing the credentials for third party services such
	| as Stripe, Mailgun, Mandrill, and others. This file provides a sane
	| default location for this type of information, allowing packages
	| to have a conventional place to find your various credentials.
	|
	*/
	
	'braintree' => [
		'model'       => 'App\User',
		'environment' => env('APP_DEBUG') ? env('BRAINTREE_ENVIRONMENT', "sandbox") : env('BRAINTREE_ENVIRONMENT', "production"),
		'merchant'    => env('APP_DEBUG') ? env('BRAINTREE_TEST_MERCHANT', false) : env('BRAINTREE_LIVE_MERCHANT', true),
		'public'      => env('APP_DEBUG') ? env('BRAINTREE_TEST_PUBLIC', false) : env('BRAINTREE_LIVE_PUBLIC', true),
		'secret'      => env('APP_DEBUG') ? env('BRAINTREE_TEST_SECRET', false) : env('BRAINTREE_LIVE_SECRET', true),
	],
	
	'mailgun' => [
		'domain' => '',
		'secret' => '',
	],
	
	'mandrill' => [
		'secret' => '',
	],
	
	'ses' => [
		'key' => '',
		'secret' => '',
		'region' => 'us-east-1',
	],
	
	'stripe' => [
		'model'  => 'App\User',
		'secret' => env('APP_DEBUG') ? env('STRIPE_TEST_SECRET', false) : env('STRIPE_LIVE_SECRET', true),
	],
	
];
