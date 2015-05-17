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
	
	/**
	 * Create a Thread / Post a Reply Form
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
	
	
	/**
	 * Post View
	 */
	// Default Values
	'anonymous'         => "Anonymous",
	
	// The direct link to a post, like No. 11111
	'post_number'       => "No.",
	
	
	// Post Actions
	'action_delete'     => "Delete",
	'action_edit'       => "Edit",
	'action_sticky'     => "Sticky",
	'action_unsticky'   => "Unsticky",
	
	
	/**
	 * Thread View
	 */
	
	// These fit together as "Omitted 3 posts" or "Omitted 3 posts with 2 files"
	// with pluralized localizations.
	'omitted_text_only' => 'Omitted :text_posts',
	'omitted_text_both' => 'Omitted :text_posts with :text_files',
	'omitted_replies'   => '{0}|{1}:number_posts post|[2,Inf]:number_posts posts',
	'omitted_file'      => '{0}|{1}:number_files file|[2,Inf]:number_files files;',
	
	
	/**
	 * Pagination
	 */
	// These are the titles that appear when hovering over items.
	'first'    => 'First',
	'previous' => 'Previous',
	'next'     => 'Next',
	'last'     => 'Last',
];
