<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Widgets
	|--------------------------------------------------------------------------
	|
	| Any language content regarding JavaScript enabled features
	|
	*/
	
	/**
	 * Main JavaScript Widget Master
	 */
	'main' => [
		'title' => "Main",
	],
	
	/**
	 * Auto-updater
	 */
	'autoupdater' => [
		'title' => "Thread Autoupdater",
		
		'enable' => "Stream new replies",
		'update' => "Update",
		'updating' => "Updating ...",
	],
	
	/**
	 * Lazy Image Loader
	 */
	'lazyimg' => [
		'title' => "Lazy Images",
		
		'desc' => "Thumbnails can be set to not load until you can see them." .
			"This can improve your overall render times and save bandwidth.",
		
		'option' => [
			'enable' => "Lazy load thumbnails",
		], 
	],
	
	/**
	 * Stylist
	 */
	'stylist' => [
		'title' => "Stylist",
	],
];
