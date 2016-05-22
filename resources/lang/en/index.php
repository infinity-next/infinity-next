<?php

return [
	'title' => [
		'welcome'       => "Welcome to :site_name",
		'statistics'    => "Site Statistics",

		'featured_post' => "Featured Post",
		'recent_images' => "Recent Images",
		'recent_posts'  => "Recent Posts",
	],

	'warning' => "Warning: Some boards on this site might contain content of an adult or offensive nature. " .
		"Please cease use of this site if it is illegal for you to view such content. " .
		"The boards on this site are made entirely by the users and do not represent the opinions of the administration of Infinity. " .
		"In the interest of free speech, only content that directly violates the DMCA or other US laws is deleted.",

	'info' => [
		'welcome' => "<p>This site uses <a href=\"https://github.com/infinity-next/infinity-next\">Infinity Next</a>, " .
			"a PHP based imageboard suite on the <a href=\"https://laravel.com\">Laravel Framework</a>. " .
			"Licensed under AGPL 3.0, anyone may download and setup an instance of Infinity Next on their own.</p>" .
			"<p>By default, the board <a href=\"/test/\">/test/</a> is installed for you to play with.</p>",

		'statistic' => [
			// These items are pluralized first, then submitted as the board and post strings to the below definitions.
			'post_count' => "{1}<strong>:posts</strong> post|[0,Inf]<strong>:posts</strong> posts",
			'board_count' => "{1}<strong>:boards</strong> board|[0,Inf]<strong>:boards</strong> boards",

			// {1} if there is only 1 board, the rest if there are >1 board.
			'boards' => "{1}There is a single board.|[0,Inf]There are currenctly :boards_public public and :boards_total total.",
			'posts'  => "Site-wide, :recent_posts have been made in the last day.",
			'posts_all' => ":posts_total have made on all active boards since :start_date",
		],
	]
];
