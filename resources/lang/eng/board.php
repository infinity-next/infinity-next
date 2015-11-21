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
		
		'report'            => "Report post",
		'report+global'     => "Report post to global staff",
	],
	
	// Form Fields
	// Specific fields in the form
	'field'               => [
		'subject'         => "Subject",
		'author'          => "Author",
		'email'           => "Email",
		'capcode'         => "No capcode",
		'download'        => "Download",
		
		'file-dz'         => "Click or drag files here to upload",
		'spoilers'        => "Spoilers",
		
		'ip'              => "IP",
		'ip_range'        => "Range",
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
		
		'report'            => "Report post",
		'report+global'     => "Report post to global staff",
	],
	
	/**
	 * Mod Tools
	 */
	'report'            => [
		'success'     => "Report submitted successfully.",
		
		'desc-local'  => "Board staff guidelines for reports ...",
		'local'       => "You are reporting a post to the local board management. " .
		                 "This usually means that the post is in violation of board-specific rules, " .
		                 "disparages the spirit of the board, or disrupts conversation.",
		
		
		'desc-global' => "Guidelines for global reports ...",
		'global'      => "You are reporting this post to <strong>global management</strong>. " .
		                 "If a post is in violation of a rule applied to all boards on a site, this is the appropriate action. " .
		                 "More frivilous or borad-specific rule violations should be handled by board staff.",
		
		'associate'            => "Associate report with your account",
		'associate-no-acct'    => "Register an account to take credit for your reports",
		'associate-disclaimer' => "<p>Any reports associated with your account will follow you. " .
		                          "Whether the report results in action will become available information to board owners or administrators if you apply for staff positions. " .
		                          "This success ratio may increase (or decrease) the likelihood of you being accepted into that role.</p>" .
		                          "<p>Your reports are not public information. Your identity will not be shown alongside your report. " .
		                          "Your report history will not become available unless you apply for a staff position and opt in to have it displayed.</p>" .
		                          "<p>If you do not want to have this report associated with your account, do not check the checkbox.</p>",
		
		'is_not_associated'    => "Anonymous report",
		'is_associated'        => "User associated report",
		
		'pending'     => "Your report has been received and is awaiting review.",
		'dismissed'   => "The report has been dismissed without action.",
		'successful'  => "The reported post has been dealt with.",
		
		'reason'      => "Reason",
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
		'history'    => "View author history",
		
		// Translator's Note:
		// This is a bit silly. It just means the poster
		// found the site via "Adventure!" mode. This can be
		// translated to anything else.
		'adventurer' => "They came from outer space!",
	],
	
	
	// Post Actions
	'action'            => [
		'view'              => "Open", // Open a thread in the catalog
		'open'              => "Actions", // List of actions
		
		'ban'               => "Ban",
		'ban_delete'        => "Ban &amp; Delete",
		'ban_delete_board'  => "Ban &amp; Delete Board-wide",
		'ban_delete_global' => "Ban &amp; Delete Site-wide",
		'bumplock'          => "Bumplock",
		'unbumplock'        => "Un-Bumplock",
		'delete'            => "Delete",
		'delete_board'      => "Delete Board-wide",
		'delete_global'     => "Delete Site-wide",
		'feature_global'    => "Feature Site-wide",
		'lock'              => "Lock",
		'unlock'            => "Unlock",
		'edit'              => "Edit",
		'sticky'            => "Sticky",
		'unsticky'          => "Unsticky",
		'report'            => "Report",
		'report_global'     => "Report Globally",
	],
	
	'ban'               => [
		'no_ip'        => "There is no IP associated with this post which you can ban.",
		
		// The number of IP addresses affected by a range ban.
		'ip_range_32'  => "{0}All IPv4 Addresses|[1,31]/:mask (:ips IPs)|{32}/:mask (:ips IP)",
		'ip_range_128' => "{0}All IPv6 Addresses|[1,127]/:mask (:ips IPs)|{128}/:mask (:ips IP)",
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
	
	/**
	 * SFW
	 */
	'sfw'      => "Safe for work only",
	'nsfw'     => "Not safe for work allowed",
];
