<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Thread, & Post Language File
	|--------------------------------------------------------------------------
	|
	| Any language content regarding the static, final print of threads and
	| posts belong here.
	|
	*/
	
	// Form Legends
	// These appear above a post form.
	'form_reply'        => "Reply",
	'form_thread'       => "New Thread",
	
	// Form Fields
	// Specific fields in the form
	'field_subject'     => "Subject",
	'field_author'      => "Author",
	'field_email'       => "Email",
	
	// Form Actions
	'action_reply'      => "Post Reply",
	'action_thread'     => "Create Thread",
	
	
	// Default Values
	'anonymous'         => "Anonymous",
	
	// These fit together as "Omitted 3 posts" or "Omitted 3 posts with 2 files"
	// with pluralized localizations.
	'omitted_text_only' => 'Omitted :text_posts',
	'omitted_text_both' => 'Omitted :text_posts with :text_files',
	'omitted_replies'   => '{0}|{1}:number_posts post|[2,Inf]:number_posts posts',
	'omitted_file'      => '{0}|{1}:number_files file|[2,Inf]:number_files files;',
];
