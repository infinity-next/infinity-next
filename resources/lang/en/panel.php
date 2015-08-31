<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Panel
	|--------------------------------------------------------------------------
	|
	| Generic control panel lines.
	|
	*/
	
	'authed_as' => "Signed in as :name",
	
	'error' => [
		'auth'  => [
			'csrf_token' => "The control panel requires cookies to be enabled.",
		],
		'board' => [
			'create_more_than_max' => "{0,1}You may not create more than 1 board.|[2,Inf]You may not create more than :boardCreateMax boards.",
			'create_so_soon'       => "{0,1}You must wait 1 minute before creating another board.|[2,Inf]You must wait :boardCreateTimer minutes before creating another board.",
		]
	],
	
	'title' => [
		'board'              => "/:board_uri/ Board Configuration",
		'site'               => "Site Configuration",
		'board_create'       => "Create a Board",
		'board_create_your'  => "Create your Board",
		'board_staff_list'   => "/:board_uri/ Staff List",
		'permissions'        => ":role Role Permissions",
		'you_are_banned'     => "You are BANNED!",
		'you_are_not_banned' => "You are not banned.",
		'reports'            => "Open Reports",
	],
	
	'nav'       => [
		'primary' => [
			'home'  => "Home",
			'board' => "Boards",
			'site'  => "Site",
			'users' => "Users",
		],
		
		'secondary' => [
			'home'   => [
				'account'         => "Account",
				'password_change' => "Change Password",
				
				'sponsorship'     => "Sponsorship",
				'donate'          => "Send Cash Contribution",
			],
			
			'site'   => [
				'setup'           => "Setup",
				'config'          => "Config",
			],
			
			
			'board'  => [
				'boards'          => "Boards",
				'config'          => "Config",
				'create'          => "Create a Board",
				
				'discipline'      => "Discipline",
				'reports'         => "Reports",
			],
			
			'users'  => [
				'permissions'     => "Permissions",
				'role_permissions' => "Role Permissions",
			],
		],
		
		'tertiary' => [
			'board_settings' => [
				'assets'  => "Assets",
				'basic'   => "Basic Details",
				'staff'   => "Staff",
				'style'   => "Styling",
			],
		],
	],
	
	'field'    => [
		'email'                => "E-Mail Address",
		'login'                => "Login",
		'logout'               => "Logout",
		'password'             => "Password",
		'password_confirm'     => "Confirm Password",
		'password_current'     => "Current Password",
		'password_new'         => "New Password",
		'password_new_confirm' => "Confirm New Password",
		'remember'             => "Remember Me",
		'uid'                  => "Username or Email",
		'username'             => "Username",
		'staff_count'          => "{0}No staff|{1}1 staff member|[2,Inf]:staff_count staff members",
		
		'login_link'           => [
			'password_forgot'      => "Forgot Password",
			'register'             => "Register an account",
		],
		
		'desc'                 => [
			'email' => "This field is optional, but is required to reset your password.",
		],
	],
	
	'bans'      => [
		'ban_list_desc' => "<p>This is a list of bans applied to your IP address. " .
						"You may be affected by a ban not intended for you, especially if on a public computer, network, or using a VPN, Proxy, or Tor. " .
						"Some bans are applied to entire ranges and will be denoted with a <a href=\"https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing\">CIDR integer</a>.</p>" .
						"<p>Sometimes, you may appeal a ban to the staff responsible for it. " .
						"If you can, a link will be visible in the ban row that goes to the appeals page.</p>",
		
		'table' => [
			'board'      => "Banned In",
			'ban_ip'     => "Banned IP",
			'ban_appeal' => "Appeal Status",
			'ban_user'   => "Moderator",
			'ban_placed' => "Placed On",
			'ban_expire' => "Expires At",
		],
		
		'ban_global' => "All Boards",
		
		'appeal_open' => "Appeals Open",
	],
	
	'reports'   => [
		'empty'          => "You have no pending reports to review.",
		'dismisssed'     => "{1}Report dismissed.|[2,Inf]Dismissed :reports reports.",
		
		'dismiss_post'   => "Dismiss Post",
		'dismiss_ip'     => "Dismiss IP",
		'dismiss_single' => "Dismiss",
		
		'promote_post'   => "Promote Post",
	],
	
	'adventure' => [
		'go'  => "ADVENTURE!",
		'sad' => "There's no where to go for an adventure. :(",
	],
	
	'password'  => [
		'reset' => "Reset Password",
		
	],
];