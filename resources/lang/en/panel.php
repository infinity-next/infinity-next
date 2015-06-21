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
				'donate'          => "Donate",
			],
			
			'site'   => [
				'setup'           => "Setup",
				'config'          => "Config",
			],
			
			
			'board'  => [
				'boards'          => "Boards",
				'config'          => "Config",
				'create'          => "Create a Board",
			],
			
			'users'  => [
				'permissions'     => "Permissions",
				'role_permissions' => "Role Permissions",
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
		
		'login_link'           => [
			'password_forgot'      => "Forgot Password",
			'register'             => "Register an account",
		],
		
		'desc'                 => [
			'email' => "This field is optional, but is required to reset your password.",
		],
	],
	
	'password'  => [
		'reset' => "Reset Password",
		
	],
];