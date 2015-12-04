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
	
	'confirm'      => "Please confirm your action.",
	'create'       => "Create",
	'upload'       => "Upload",
	'delete'       => "Delete",
	'submit'       => "Save Changes",
	'any_language' => "International",
	
	'locked'       => "This setting has been locked by an admin.",
	'locking'      => "Locks this setting value to users without special permissions.",
	
	
	'legend' => [
		'asset'        => [
			'board_icon'       => "Overboard Icon",
			'file_deleted'     => "Attachment Deleted",
			'file_spoiler'     => "Spoiler",
		],
		'permissions'   => [
			'board_controls'   => "Board Controls",
			'board_images'     => "Images",
			'board_moderation' => "Board Moderation",
			'board_posts'      => "Posting",
			'board_users'      => "User Tools",
			'site_tools'       => "Site Tools",
			'system_tools'     => "System Tools",
		],
		
		'account_basic'      => "Account Details",
		'account_existing'   => "Existing Username",
		'attachments'        => "Attachment Options",
		'bans'               => "Ban Options",
		'board_language'     => "Language",
		'reports'            => "Report Options",
		'role_basic'         => "Role Details",
		'staff_castes'       => "Castes",
		
		'adventures'         => "Adventure Options",
		'boards'             => "Board Options",
		'board_banned'       => "Ban Page Images",
		'board_banners'      => "Board Banners",
		'board_basic'        => "Basic Details",
		'board_diplomacy'    => "Board Diplomacy",
		'board_ephemerality' => "Content Ephemerality",
		'board_flags'        => "Custom Flags",
		'board_originality'  => "Content Originality",
		'board_posts'        => "Post Options",
		'board_threads'      => "Thread Options",
		'board_tags'         => "Board Tags",
		'captcha'            => "Captcha Settings",
		'sidebar'            => "Sidebar",
		'site'               => "Site",
		'style'              => "Styling",
		
		'navigation'         => "Site Navigation",
	],
	
	'option' => [
		'siteName'                 => "Site Name",
		'board_uri'                => "URI",
		'title'                    => "Title",
		'description'              => "Subtitle",
		
		'boardBasicUri'            => "URI",
		'boardBasicTitle'          => "Title",
		'boardBasicDesc'           => "Description",
		'boardBasicOverboard'      => "Stream to Overboard",
		'boardBasicIndexed'        => "Publicly Indexed",
		'boardBasicWorksafe'       => "Safe for Work",
		'boardCustomCSSEnable'     => "Enable Custom CSS",
		'boardCustomCSSSteal'      => "Borrow Custom CSS from Board",
		'boardCustomCSS'           => "Custom CSS",
		'boardLanguage'            => "Primary Language",
		'boardSidebarText'         => "Content",
		'boardUriBanned'           => "Banned Board URIs",
		'boardTags'                => "Tags",
		
		'adventureEnabled'         => "Enable adventures",
		'adventureIcons'           => "Show icons for adventure posts",
		
		'boardAssetBannerUpload'   => "Upload new board banner",
		'boardAssetbannedUpload'   => "Upload new banned image",
		'boardAssetFlagUpload'     => "Upload new custom flag",
		
		'boardCreateMax'           => "Maximum boards per user",
		'boardCreateTimer'         => "Cooldown between board creations (min)",
		
		'boardListShow'            => "Show top boards in primary navigation",
		
		'attachmentName'           => "Attachment download names",
		'attachmentFilesize'       => "Maximum filesize (KiB)",
		'attachmentThumbnailJpeg'  => "Compress thumbnails to JPEG",
		'attachmentThumbnailQuality' => "Image quality (percent)",
		'attachmentThumbnailSize'  => "Attachment preview size (px)",
		'banMaxLength'             => "Maximum length for bans (days)",
		'banSubnets'               => "Allow subnet bans",
		
		'captchaEnabled'           => "Enable CAPTCHAs",
		'captchaLifespanTime'      => "Lifespan of solutions in minutes",
		'captchaLifespanPosts'     => "Lifespan of solutions in posts made",
		
		'boardReportText'          => "Message to users creating a local report",
		'globalReportText'         => "Message to users creating a global report",
		
		'postAnonymousName'        => "Default author name for anonymous posts",
		'postAttachmentsMax'       => "Maximum attachments per post",
		'postAttachmentsMin'       => "Minimum attachments per post",
		'threadAttachmentsMin'     => "Minimum attachments for new threads",
		'postMaxLength'            => "Maximum characters per post",
		'postMinLength'            => "Minimum characters per post",
		'postNewLines'             => "Maximum linebreaks per post",
		'postFloodTime'            => "Minimum time between posts (sec)",
		'threadFloodTime'          => "Minimum time between threads (sec)",
		'postsAllowSubject'        => "Allow subject field",
		'postsAllowAuthor'         => "Allow author field",
		
		'epheSageThreadReply'      => "Autosage threads after this many replies",
		'epheSageThreadDays'       => "Autosage threads after this many days",
		'epheSageThreadPage'       => "Autosage threads on this page",
		'epheLockThreadReply'      => "Lock threads after this many replies",
		'epheLockThreadDays'       => "Lock threads after this many days",
		'epheLockThreadPage'       => "Lock threads on this page",
		'epheDeleteThreadReply'    => "Delete threads after this many replies",
		'epheDeleteThreadDays'     => "Delete threads after this many days",
		'epheDeleteThreadPage'     => "Delete threads on this page",
		'ephePostIpLife'           => "Delete post IPs after this many days",
		'ephePostHardDelete'       => "Permanently delete posts after this many days",
		'epheMediaPrune'           => "Delete orphaned media after this many days",
		
		'originalityImages'        => "Image originality enforcement",
		'originalityPosts'         => "Post content originality enforcement",
		
		'roleType'                 => "Role",
		'roleName'                 => "Internal Name",
		'roleCapcode'              => "Capcode",
		'roleCaste'                => "Caste",
		
		'threadRequireSubject'     => "Threads require subject",
		'postsPerPage'             => "Threads per page",
		'postsAuthorCountry'       => "Show author country flags",
		'postsThreadId'            => "Show thread author IDs",
		
		'boardBacklinksCrossboard' => "Allow any board to backlink posts",
		'boardBacklinksBlacklist'  => "Forbid these boards from backlinking",
		'boardBacklinksWhitelist'  => "Always allow these boards to backlink",
		
		'desc' => [
			'board_uri'                => "Part of the URL used to open your board. Cannot be changed.",
			
			'attachmentName'           => "<tt>%t</tt> for the UNIX timestamp of post creation.<br />" .
			                              "<tt>%i</tt> for the attachment's index on this post.<br />" .
			                              "<tt>%n</tt> for the user's given filename.<br />",
			
			'boardCustomCSSSteal'      => "If enabled, your board will begin borrowing its style from another board.<br />" .
			                              "If this board is also borrowing CSS, you will get nothing.<br />" .
			                              "Check their style.txt to see if they're also borrowing.",
			
			'boardBacklinksCrossboard' => "If enabled, any board off your blacklist may backlink posts.<br />" .
			                              "If disabled, only boards on your whitelist may backlink posts.",
			
			'boardBacklinksBlacklist'  => "One board per line, only text, no slashes.",
			'boardBacklinksWhitelist'  => "One board per line, only text, no slashes.",
			
			'threadAttachmentsMin'     => "If there are also minimum attachments per post, <wbr />the greater of the two will be used.",
			
			'postNewLines'             => "Useful if spammers use many new lines to disrupt board flow.<br/>0 is no maximum.",
			
			'captchaEnabled'           => "Enables CAPTCHA tests for posts",
			'captchaLifespanTime'      => "After this much time (in minutes), the captcha will expire.",
			'captchaLifespanPosts'     => "After this many posts, the captcha will expire.",
			
			'roleType'                 => "This role's group. Staff groups apply only to people they're assigned to.<br />Anonymous types will apply to all people who visit your board.",
			'roleName'                 => "Your name for this group. Internal use only.",
			'roleCapcode'              => "Capcodes sign a post with authority. Leave blank for no capcode.",
			'roleCaste'                => "A caste is used to separate different staff roles.",
			
			'originalityImages'        => "Will reject images being uploaded depending on location and setting.",
			'originalityPosts'         => "Will reject posts if similar messages have been posted depending on location and setting.<br />" .
			                              "<em>ROBOT9000</em> will <a href=\"http://blog.xkcd.com/2008/01/14/robot9000-and-xkcd-signal-attacking-noise-in-chat/\">autoban offenders</a> and is board-wide.<br />" .
			                              "<em>Respect the Robot!</em> is R9K mode that bans for duplicate content found anywhere <em>on the entire site</em>, even boards without R9K mode.",
		],
	],
	
	'choices'                  => [
		'originalityImages' => [
			'thread' => "No duplicate images per thread.",
			'board'  => "No duplicate images for entire board.",
		],
		'originalityPosts'  => [
			'board'    => "No duplicate messages for entire board.",
			'site'     => "No duplicate messages found anywhere on the site.",
			'boardr9k' => "ROBOT9000",
			'siter9k'  => "Respect the robot!",
		],
	],
	
	'permission' => [
		'master' => [
			'help' => [
				'quickcheck' => "Quick check all",
				
				'inherit'    => "Defaults to 'no', but will inherit a 'yes' from parent roles.",
				'allow'      => "Give the role permission.",
				'deny'       => "Never allows this role to have this permission, even if a parent role allows it.",
			],
			
			'inherit' => "Inherit",
			'allow'   => "Allow",
			'revoke'  => "Revoke",
			'deny'    => "Never",
		],
		
		'board' => [
			'bans'   => "View board bans",
			'logs'   => "View staff logs",
			'config' => "Edit board config",
			'create' => "Create board",
			'delete' => "Delete board",
			
			'image' => [
				'ban' => "Ban attachments",
				'delete' => [
					'other' => "Delete anyone's attachments",
					'self' => "Delete own attachments",
				],
				'spoiler' => [
					'other' => "Spoiler anyone's attachments",
					'upload' => "Spoiler own attachments",
				],
				'upload' => [
					'new' => "Upload new attachments",
					'old' => "Upload recognized attachments",
				],
			],
			
			'post' => [
				'create' => [
					'thread' => "Post threads",
					'reply'  => "Post replies",
				],
				'delete' => [
					'other' => "Delete anyone's content",
					'self' => "Delete own content",
				],
				'edit' => [
					'other' => "Edit anyone's content",
					'self' => "Edit own content",
				],
				'lock' => "Lock anyone's threads",
				'bumplock' => "Bumplock anyone's threads",
				'sticky' => "Sticky anyone's threads",
				
				'lock_bypass' => "Post in locked threads",
				
				'report' => "Report post to board managers",
			],
			
			'reassign' => "Reassign board",
			
			'user' => [
				'ban' => [
					'free' => "Ban IP freely for any reason ",
					'reason' => "Ban IP for post and reason",
				],
				'role' => "Assign board roles to user",
				'unban' => "Unban IP",
				'raw_ip' => "View raw IPs",
			],
			
		],
		
		'site' => [
			'board' => [
				'view_unindexed' => "Find Unindexed Boards",
			],
			
			'image' => [
				'ban' => "Ban image checksums",
			],
			
			'pm' => "PM users",
			
			'post' => [
				'report' => "Report post to global managers",
			],
			
			'user' => [
				'create' => "Create user",
				'merge'  => "Merge user into own account",
				'raw_ip' => "View real IPs",
			],
		],
		
		'sys' => [
			'boards' => "Edit other's boards",
			'cache' => "Clear system cache",
			'config' => "Edit system config",
			'logs' => "View and manipulate logs",
			'nocaptcha' => "Post without CAPTCHAs",
			'payments' => "Edit payments and donations",
			'permissions' => "Edit role permissions",
			'roles' => "Edit roles",
			'tools' => "Access system tools",
			'users' => "Manage users",
		],
	],
];
