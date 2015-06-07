<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Site & Board Config
	|--------------------------------------------------------------------------
	|
	| The names and descriptions of important site and board options.
	|
	*/
	
	'title' => [
		'board' => "Board Configuration",
		'site'  => "Site Configuration",
		'board_create' => "Create a Board",
	],
	
	'legend' => [
		'attachments' => "Attachment Options",
		'bans'        => "Ban Options",
		
		'board_posts'   => "Post Options",
		'board_threads' => "Thread Options",
		'board_basic'   => "Basic Details",
	],
	
	'option' => [
		'board_uri'               => "URI",
		'title'                   => "Title",
		'description'             => "Subtitle",
		
		'attachmentFilesize'      => "Maximum filesize (KiB)",
		'attachmentThumbnailSize' => "Attachment preview size (px)",
		'banMaxLength'            => "Maximum length for bans (days)",
		'banSubnets'              => "Allow subnet bans",
		
		'postAttachmentsMax'      => "Maximum attachments per post",
		'postMaxLength'           => "Maximum characters per post",
		'postMinLength'           => "Minimum characters per post",
		
		'postsPerPage'            => "Threads per page",
		
		'desc' => [
			'board_uri' => "Part of the URL used to open your board. Cannot be changed.",
		],
	],
	
	'submit' => "Save Changes",
];
