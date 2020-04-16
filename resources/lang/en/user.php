<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Users
	|--------------------------------------------------------------------------
	|
	| Any language content regarding user profiles and details.
	|
	*/

	/**
	 * Staff Roles and Capcodes
	 * Used for "## Capcodes" and internal purposes.
	 */
	'role' => [
		'anonymous'     => "Anonymous",
		'admin'         => "Administrator",
		'global_mod'    => "Global Volunteer",
		'unaccountable' => "Proxy User",
		'registered'    => "Registered User",
		'absolute'      => "Global Absolute",

		// This is a bit of a hack using the pluralization system.
		// If we have 1 or more, we are identifying the role as "## /b/ Volunteer".
		// If we have 0, we're simply identifying the role as a "# Board Volunteer".
		// Show posessive.
		'board_owner'   => "{0}Board Owner|[1,*]/:board_uri/ Owner",
		'board_mod'     => "{0}Board Volunteer|[1,*]/:board_uri/ Volunteer",
	],

];
