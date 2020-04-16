<?php

return [
	'title' => [
		'welcome'       => ":site_name wita!",
		'statistics'    => "Statystyki strony",

		'featured_post' => "Promowany post",
		'recent_images' => "Ostatnie obrazki",
		'recent_posts'  => "Ostatnie posty",
	],

	'warning' => "Warning: Some boards on this site might contain content of an adult or offensive nature.<wbr />" .
		"Please cease use of this site if it is illegal for you to view such content.<wbr />" .
		"The boards on this site are made entirely by the users and do not represent the opinions of the administration of Infinity.<wbr />" .
		"In the interest of free speech, only content that directly violates the DMCA or other US laws is deleted.<wbr />",

	'info' => [
		'welcome' => "<p>Ta strona używa <a href=\"https://github.com/infinity-next/infinity-next\">Infinity Next</a>, " .
			"oprogramowania do tworzenia for obrazkowych opartego na <a href=\"https://laravel.com\">frameworku Laravel</a>. " .
			"Infinity Next jest wydany na licencji AGPL 3.0, co oznacza, że każdy może ściągnąć i zainstalować instancję tego silnika samemu.</p>" .
			"<p>Board <a href=\"/test/\">/test/</a> został utworzony.</p>",

		'statistic' => [
			'boards' => "{1}Jest tylko jeden board.|[0,*]Obecnie jest :boards_public publicznych i :boards_total razem.",
			'posts'  => "Wczoraj na całej stronie zostało utworzonych :recent_posts.",
			'posts_all' => "Od :start_date zostało utworzonych :posts_total",

			// Inserted into the above values. A post/board count is wrapped here, then concatenated above.
			'post_count' => "<strong>:posts</strong> post|<strong>:posts</strong> posty|<strong>:posts</strong> postów",
			'board_count' => "<strong>:boards</strong> board|<strong>:boards</strong> boardy|<strong>:boards</strong> boardów",
		],
	]
];
