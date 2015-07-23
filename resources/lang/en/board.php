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
	'legend'            => [
		"edit"   => "Edit Post",
		"reply"  => "Reply",
		"thread" => "New Thread",
		
		"ban"               => "Ban user from :board for post",
		"ban+global"        => "Ban user from entire site for post",
		"ban+delete"        => "Ban user and delete post",
		"all+ban+delete"    => "Ban user and delete all their posts on :board",
		"ban+delete+global" => "Ban user and wipe all their posts from entire site",
	],
	
	// Form Fields
	// Specific fields in the form
	'field'             => [
		'subject'     => "Subject",
		'author'      => "Author",
		'email'       => "Email",
		
		'ip'              => "IP",
		'justification'   => "Reason",
		'expires'         => "Ban Expiry",
		'expires-days'    => "Days",
		'expires-hours'   => "Hours",
		'expires-minutes' => "Minutes",
	],
	
	// Form Submit Buttons
	'submit'            => [
		"edit"   => "Submit Modification",
		"reply"  => "Post Reply",
		"thread" => "Create Thread",
		
		"ban"               => "Submit :board ban",
		"ban+global"        => "Submit global ban",
		"ban+delete"        => "Submit :board ban and delete post",
		"all+ban+delete"    => "Submit :board ban and delete user's posts",
		"ban+delete+global" => "Submit global ban and wipe user posts",
	],
	
	
	/**
	 * Post View
	 */
	// Default Values
	'anonymous'         => "Anonymous",
	
	// The direct link to a post, like No. 11111
	'post_number'       => "No.",
	
	// Details
	'detail'            => [
		'sticky'     => "Stickied",
		'bumplocked' => "Bumplocked",
		'locked'     => "Locked",
		'deleted'    => "Deleted",
	],
	
	
	// Post Actions
	'action'            => [
		'open'              => "Actions",
		
		'ban'               => "Ban",
		'ban_delete'        => "Ban &amp; Delete",
		'ban_delete_board'  => "Ban &amp; Delete Board-wide",
		'ban_delete_global' => "Ban &amp; Delete Site-wide",
		'bumplock'          => "Bumplock",
		'unbumplock'        => "Un-Bumplock",
		'delete'            => "Delete",
		'delete_board'      => "Delete Board-wide",
		'delete_global'     => "Delete Site-wide",
		'lock'              => "Lock",
		'unlock'            => "Unlock",
		'edit'              => "Edit",
		'sticky'            => "Sticky",
		'unsticky'          => "Unsticky",
	],
	
	'meta'              => [
		'banned'            => "User was banned for this post.",
		'banned_for'        => "User was banned for this post. Reason: <em>:reason</em>",
		'updated_by'        => "This post was last edited by :name at :time.",
	],
	
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
