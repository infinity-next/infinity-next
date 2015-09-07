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
	 * @var string  /route/to/captcha/
	 */
	'route'    => "cp/captcha",
	
	/**
	 * Font data.
	 *
	 * @var array
	 */
	'font'     => [
		/**
		 * Font file locations.
		 *
		 * @var array  of file paths relative to application base
		 */
		'files' => [
			'vendor/infinity-next/brennan-captcha/fonts/Cedarville_Cursive/Cedarville-Cursive.ttf',
			'vendor/infinity-next/brennan-captcha/fonts/Gochi_Hand/GochiHand-Regular.ttf',
			'vendor/infinity-next/brennan-captcha/fonts/Just_Another_Hand/JustAnotherHand.ttf',
			'vendor/infinity-next/brennan-captcha/fonts/Patrick_Hand/PatrickHand-Regular.ttf',
			'vendor/infinity-next/brennan-captcha/fonts/Patrick_Hand_SC/PatrickHandSC-Regular.ttf',
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
			'charset'    => 'AaBbCcDdEeFGHhIiJjKkLlMmNnOoPpQRrSsTtUuVvWwXxYyZ',
			
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
			 * CAPTCHA sizes can vary a bit depending on font sizes and font metrics, but these are minimums.
			 * Minimum image width.
			 *
			 * @var int
			 */
			'width_min'   => 280,
			
			/**
			 * Maximum image width.
			 *
			 * @var int
			 */
			'height_min'  => 70,
			
			/**
			 * Maximum font size in pixels.
			 *
			 * @var int
			 */
			'font_size'   => 40,
		],
		
	],
];