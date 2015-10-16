<?php

return [
	
	/*
	|--------------------------------------------------------------------------
	| Navigation
	|--------------------------------------------------------------------------
	|
	| Navigation for all systems throughout the site.
	|
	*/
	
	'navigation'  => "<i class=\"fa fa-bars\"></i>&nbsp;Navigation",
	
	'global'      => [
		'flyout'       => [
			'popular_boards'  => "Popular Boards",
			'recent_boards'   => "Recently Active Boards",
			'favorite_boards' => "Favorite Boards",
		],
		
		'home'         => "Home",
		'panel'        => "Panel",
		'boards'       => "Boards",
		'new_board'    => "Create Board",
		'contribute'   => "Contribute",
		'donate'       => "Fund Us",
		'adventure'    => "Adventure",
		// TN: 'Overboard' has a specific meaning to English IB users.
		// Feel free to translate to Recent Posts instead.
		'recent_posts' => "Overboard",
	],
	
	'panel'       => [
		'primary' => [
			'home'     => "Home",
			'board'    => "Boards",
			'site'     => "Site",
			'users'    => "Users",
			'logout'   => "Logout",
			'register' => "Register",
			'login'    => "Login",
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
				'create'          => "Create a Board",
				
				'boards'          => "Boards",
				'assets'          => "Assets",
				'config'          => "Config",
				'staff'           => "Staff",
				
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
				'roles'   => "Roles",
				'staff'   => "Staff",
				'style'   => "Styling",
			],
		],
	],
];