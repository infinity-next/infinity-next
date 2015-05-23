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
	],
	
	'legend' => [
		'attachments' => "Attachment Options",
		'bans'        => "Ban Options",
	],
	
	'option' => [
		'attachmentFilesize'      => "Maximum filesize (KiB)",
		'attachmentThumbnailSize' => "Attachment preview size (px)",
		'banMaxLength'            => "Maximum length for global bans (days)",
		'banSubnets'              => "Allow subnet bans",
	],
];
