<?php

return [
	
	/**
	 * The table utilized by the Brennan Captch model and migration.
	 * 
	 * @var string  table_name
	 */
	'table'    => "captcha",
	
	/**
	 * Route hooked by the captcha service.
	 * If "captcha", image URL will be:
	 * //base.url/captcha/profile/sha1.png
	 *
	 * This needs to be an actual path because we concatenate.
	 *
	 * @var string  route/to/captcha
	 */
	'route'    => "cp/captcha",
	
	/**
	 * Expiry time (in minutes) for a captcha.
	 * It's imporatnt to have a short expiry time for your images.
	 * If it's very long, it makes it easier for human captcha crackers to store answers.
	 *
	 * @var int  in minutes
	 */
	'expires_in' => 5,
	
	/**
	 * Font file locations.
	 *
	 * @var array  of file paths relative to application base
	 */
	'fonts'    => [
		[
			'file'   => 'vendor/infinity-next/brennan-captcha/fonts/Cedarville_Cursive/Cedarville-Cursive.ttf',
			'stroke' => 3,
		],
		[
			'file'   => 'vendor/infinity-next/brennan-captcha/fonts/Gochi_Hand/GochiHand-Regular.ttf',
			'stroke' => 3,
		],
		[
			'file'   => 'vendor/infinity-next/brennan-captcha/fonts/Just_Another_Hand/JustAnotherHand.ttf',
			'stroke' => 3,
		],
		[
			'file'   => 'vendor/infinity-next/brennan-captcha/fonts/Patrick_Hand/PatrickHand-Regular.ttf',
			'stroke' => 3,
		],
		[
			'file'   => 'vendor/infinity-next/brennan-captcha/fonts/Patrick_Hand_SC/PatrickHandSC-Regular.ttf',
			'stroke' => 5,
		],
	],
	
	/**
	 * Captcha image profiles.
	 *
	 * @var array  of arrays
	 */
	'profiles' => [
		
		/**
		 * The default captcha profile.
		 * All settings available to you are demonstrated here.
		 *
		 * @var array  of settings
		 */
		'default' => [
			/**
			 * Characters that can appear in the image.
			 * Solutions are case-insensitive, but charsets are.
			 * Lower-case F, G, Q and Z are omitted by default because of cursive lettering being hard to distinguish.
			 * This should also support international or ASCII characters, if you're daring enough.
			 *
			 * @var string  of individual characters
			 */
			'charset'    => 'AaBbCcDdEeFGHhIJKkLMmNnOoPpQRrSsTtUuVvWwXxYyZ1234567890',
			
			/**
			 * Valid colors for the character sets.
			 *
			 * @var array  of R,G,B colors.
			 */
			'colors'      => [
				[132, 129, 255],
				[ 92,  88, 255],
				[244, 100,  95],
				[183,  75,  71],
			],
			
			/**
			 * Color of the backdrop.
			 *
			 * @var array  R,G,B color
			 */
			'canvas'      => [255,255,255],
			
			/**
			 * Minimum characters per captcha.
			 *
			 * @var int
			 */
			'length_min'  => 6,
			
			/**
			 * Maximum characters per captcha.
			 *
			 * @var int
			 */
			'length_max'  => 8,
			
			/**
			 * Applies a sine wave effect through the captcha.
			 *
			 * @var boolean
			 */
			'sine'        => true,
			
			
			/**
			 * Captcha image width.
			 *
			 * @var int
			 */
			'width'       => 280,
			
			/**
			 * Maximum image width.
			 *
			 * @var int
			 */
			'height'      => 80,
			
			/**
			 * Maximum font size in pixels.
			 *
			 * @var int
			 */
			'font_size'   => 48,
			
		],
		
	],
];
