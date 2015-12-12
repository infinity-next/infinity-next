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
		'title' => "Main Configuration",
		
		'desc' => "These options are for core components of the website.",
		
		'option' => [
			'widget' => "Enable widgets",
		], 
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
		
		'desc' => "Thumbnails can be set to not load until you can see them. " .
			"This can improve your overall render times and save bandwidth.",
		
		'option' => [
			'enable' => "Lazy load thumbnails",
		], 
	],
	
	/**
	 * InstantClick One-Page Application
	 */
	'instantclick' => [
		'title' => "InstantClick",
		
		'desc' => "InstantClick will turn the site into a one-page " .
			"application, and most subsequent link clicks will cause the page " .
			"to load new contents without refreshing the entire document. " .
			"This can enable pages to load instantly, but on older machines " .
			"it may cause resource issues and edge-case errors.<br />".
			"<strong>Experimental, use at your own risk.</strong>",
			
		'option' => [
			'enable' => "Enable InstantClick",
		], 
	],
	
	/**
	 * Posts
	 */
	'post' => [
		'title' => "Posts",
		
		'desc' => "The post widget is very large and incorporates many " .
			"fundamental aspects to how an imageboard looks and feels.",
		
		'option' => [
			'author_id' => "Display Author IDs when available",
		],
	],
	
	
	/**
	 * Postbox
	 */
	'postbox' => [
		'title' => "Post Form",
		
		'option' => [
			'password'  => "Default post deletion password",
		],
	],
	
	/**
	 * Stylist
	 */
	'stylist' => [
		'title' => "Stylist",
	],
	
	/**
	 * Timestamps
	 */
	'time' => [
		'title' => "Timestamps",
		
		'option' => [
			'format' => "Timestamp Format",
		], 
		
		// Default PHP formatting. Different than JS.
		'format' => "%Y-%b-%d %H:%M:%S",
		
		'calendar' => [
			// Order of these item's children are important.
			// 0 must be January. 11 must be December.
			// 0 must be Sunday. 6 must be Saturday.
			'months' => [
				"January",
				"February",
				"March",
				"April",
				"May",
				"June",
				"July",
				"August",
				"September",
				"October",
				"November",
				"December",
			],
			'abbrevmonths' => [
				"Jan", "Feb", "Mar", "Apr",
				"May", "Jun", "Jul", "Aug",
				"Sep", "Oct", "Nov", "Dec",
			],
			'weekdays' => [
				"Sunday",
				"Monday",
				"Tuesday",
				"Wednesday",
				"Thursday",
				"Friday",
				"Saturday",
			],
			'abbrevdays' => [
				"Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
			],
		]
	],
];
