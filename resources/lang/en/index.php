<?php

return [
	'title' => [
		'welcome'       => "Welcome to :site_name",
		'statistics'    => "Site Statistics",

		'featured_post' => "Featured Post",
		'recent_images' => "Recent Images",
		'recent_posts'  => "Recent Posts",
	],

	'info' => [
		'welcome' => "<p>This site uses <a href=\"https://github.com/infinity-next/infinity-next\">Infinity Next</a>, " .
			"a PHP based imageboard suite on the <a href=\"https://laravel.com\">Laravel Framework</a>. " .
			"Licensed under AGPL 3.0, anyone may download and setup an instance of Infinity Next on their own.</p>" .
			"<p>By default, the board <a href=\"/test/\">/test/</a> is installed for you to play with.</p>",

		'statistic' => [
			'boards' => "{1}There is a single board.|[0,Inf]There are currently :boards_public public and :boards_total total.",
			'posts'  => "Site-wide, :recent_posts have been made in the last day.",
			'posts_all' => ":posts_total have made on all active boards since :start_date",

			// Inserted into the above values. A post/board count is wrapped here, then concatenated above.
			'post_count' => "<strong>:posts</strong> post|<strong>:posts</strong> posts",
			'board_count' => "<strong>:boards</strong> board|<strong>:boards</strong> boards",
		],
	]
];
