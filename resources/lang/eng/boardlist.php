<?php

/*
|--------------------------------------------------------------------------
| Board Directory
|--------------------------------------------------------------------------
*/

return [
	'search' => [
		'lang' => [
			'any'      => "Any Languages",
			'all'      => "All",
			'popular'  => "Popular",
		],
		
		'sfw_only' => "Hide NSFW Boards",
		'titles'   => "Search titles...",
		'tags'     => "Search tags...",
		
		'title'    => "Search",
		'find'     => "Search",
	],
	
	'table' => [
		'uri'         => "URI",
		'title'       => "Title",
		'pph'         => "PPH",
		'active'      => "Active Users",
		'tags'        => "Tags",
		'total_posts' => "Total Posts",
		
		// Translation Note:
		// The <br/> tag in this indicates a LINE BREAK.
		// This creates two lines of text that nicely segregate the word "posts" from the length of time.
		// If your language cannot do this or doesn't need to, just remove the <br/> or replace it with a space.
		'ppd'         => "Posts<br/>Per Day",
		'plh'         => "Posts<br/>Last Hour",
	],
	
	'footer' => [
		'displaying' => "Displaying results :board_current through :board_count out of :board_total.",
		'load_more'  => "Click to load more.",
	],
];