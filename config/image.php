<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Intervention/Image Configuration
	|--------------------------------------------------------------------------
	|
	| The Intervention/Image project is what we use to manipulate images.
	| This includes CAPTCHA and thumbnailing attachments.
	|
	*/
	
	'debug' => env('LIB_IMAGE', 'gd'), // gd|imagick
	
];