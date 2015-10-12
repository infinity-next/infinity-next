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
	
	'confirm' => "Please confirm your action.",
	'create'  => "Create",
	'upload'  => "Upload",
	'submit'  => "Save Changes",
	
	'legend' => [
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
		'reports'            => "Report Options",
		'role_basic'         => "Role Details",
		'staff_castes'       => "Castes",
		
		'boards'             => "Board Options",
		'adventures'         => "Adventure Options",
		'board_banners'      => "Board Banners",
		'board_basic'        => "Basic Details",
		'board_ephemerality' => "Content Ephemerality",
		'board_posts'        => "Post Options",
		'board_threads'      => "Thread Options",
		'sidebar'            => "Sidebar",
		'style'              => "Styling",
		
		'navigation'         => "Site Navigation",
	],
	
	'option' => [
		'board_uri'                => "URI",
		'title'                    => "Title",
		'description'              => "Subtitle",
		
		'boardBasicUri'            => "URI",
		'boardBasicTitle'          => "Title",
		'boardBasicDesc'           => "Description",
		'boardBasicOverboard'      => "Stream to Overboard",
		'boardBasicIndexed'        => "Publicly Indexed",
		'boardBasicWorksafe'       => "Safe for Work",
		'boardCustomCSS'           => "Custom CSS",
		'boardSidebarText'         => "Content",
		'boardUriBanned'           => "Banned Board URIs",
		
		'adventureEnabled'         => "Enable adventures",
		'adventureIcons'           => "Show icons for adventure posts",
		
		'boardAssetBannerUpload'   => "Upload new board banner",
		
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
		
		'boardReportText'          => "Message to users creating a local report",
		'globalReportText'         => "Message to users creating a global report",
		
		'postAttachmentsMax'       => "Maximum attachments per post",
		'postMaxLength'            => "Maximum characters per post",
		'postMinLength'            => "Minimum characters per post",
		'postFloodTime'            => "Minimum time between posts (sec)",
		
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
		
		'roleType'                 => "Role",
		'roleName'                 => "Internal Name",
		'roleCapcode'              => "Capcode",
		'roleCaste'                => "Caste",
		
		'postsPerPage'             => "Threads per page",
		'postsAuthorCountry'       => "Show author country flags",
		'postsThreadId'            => "Show thread author IDs",
		
		'desc' => [
			'board_uri'                => "Part of the URL used to open your board. Cannot be changed.",
			
			'attachmentName'           => "<tt>%t</tt> for the UNIX timestamp of post creation.<br />" .
			                              "<tt>%i</tt> for the attachment's index on this post.<br />" .
			                              "<tt>%n</tt> for the user's given filename.<br />",
			
			'roleType'                 => "This role's group. Staff groups apply only to people they're assigned to.<br />Anonymous types will apply to all people who visit your board.",
			'roleName'                 => "Your name for this group. Internal use only.",
			'roleCapcode'              => "Capcodes sign a post with authority. Leave blank for no capcode.",
			'roleCaste'                => "A caste is used to separate different staff roles.",
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
				'upload' => "Upload attachments",
			],
			
			'post' => [
				'create' => "Post threads and replies",
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
