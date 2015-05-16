<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class DatabaseSeeder extends Seeder {

	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run()
	{
		Model::unguard();
		
		$this->call('UserSeeder');
		$this->call('BoardSeeder');
		$this->call('PermissionSeeder');
		$this->call('RoleSeeder');
		$this->call('UserRoleSeeder');
		$this->call('RolePermissionSeeder');
	}

}


use App\User;

class UserSeeder extends Seeder {
	
	protected static $potential_pass_words = [
		"alarmed", "blue",   "cheap", "digital", // 4
		"eminent", "free",   "gross", "hairy",   // 8
		"illegal", "jolly",  "lazy",  "minor",   // 12
		"nimble",  "perky",  "quiet", "real",    // 16
		"stiff",   "tragic", "ugly",  "vital",   // 20
		"wet",     "yellow", "zesty", "key",     // 24
		"open",    "xeno", // 26
	];
	
	public function run()
	{
		$this->command->info('Creating first user ...');
		
		// Generate a password.
		$password = [];
		for ($i = 0; $i < 4; ++$i)
		{
			$password[] = static::$potential_pass_words[array_rand(static::$potential_pass_words)];
		}
		$password = implode($password, " ");
		
		// Create the user.
		$user = User::firstOrCreate([
			'user_id'  => 1,
			'username' => "Admin",
			'password' => bcrypt($password),
		]);
		
		$this->command->info("User \"Admin\" has been created with the following password: {$password}");
	}
}


use App\Board;

class BoardSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Creating first test board ...');
		
		$board = Board::firstOrCreate([
				'board_uri'   => "test",
				'title'       => "Test",
				'description' => "Discover software features on your own",
				'created_by'  => 1,
				'operated_by' => 1,
			]);
		
		$this->command->info("Board exists now. Accessible at /test/.");
	}
}


use App\Permission;

class PermissionSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Creating permission sets ...');
		
		DB::table('permissions')->insert([
			[
				'permission_id' => "board.create",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.delete",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.reassign",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.post.create",
				'base_value' => 1,
			],
			[
				'permission_id' => "board.post.delete.self",
				'base_value' => 1,
			],
			[
				'permission_id' => "board.post.delete.other",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.post.edit.self",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.post.edit.other",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.post.sticky",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.user.unban",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.user.ban.reason",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.user.ban.free",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.image.ban",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.image.upload",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.image.delete.self",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.image.delete.other",
				'base_value' => 0,
			],
			[
				'permission_id' => "board.image.spoiler.upload",
				'base_value' => 1,
			],
			[
				'permission_id' => "board.image.spoiler.other",
				'base_value' => 0,
			],
		]);
	}
}


use App\Role;

class RoleSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Adding roles ...');
		
		DB::table('roles')->insert([
			[
				'role_id'    => 1,
				'role'       => "anonymous",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'inherit_id' => NULL,
				'name'       => "Anonymous",
				'capcode'    => NULL,
				'system'     => true,
			],
			[
				'role_id'       => 2,
				'role'     => "admin",
				'board_uri' => NULL,
				'caste'    => NULL,
				'inherit_id' => NULL,
				'name'     => "Administrator",
				'capcode'  => "Administrator",
				'system'   => true,
			],
			[
				'role_id'       => 3,
				'role'     => "moderator",
				'board_uri' => NULL,
				'caste'    => NULL,
				'inherit_id' => NULL,
				'name'     => "Global Volunteer",
				'capcode'  => "Global Volunteer",
				'system'   => true,
			],
			[
				'role_id'       => 4,
				'role'     => "owner",
				'board_uri' => NULL,
				'caste'    => NULL,
				'inherit_id' => NULL,
				'name'     => "Board Owner",
				'capcode'  => "Board Owner",
				'system'   => true,
			],
			[
				'role_id'       => 5,
				'role'     => "volunteer",
				'board_uri' => NULL,
				'caste'    => NULL,
				'inherit_id' => NULL,
				'name'     => "Board Volunteer",
				'capcode'  => "Board Volunteer",
				'system'   => true,
			],
			[
				'role_id'   => 6,
				'role'      => "unaccountable",
				'board_uri' => NULL,
				'caste'     => NULL,
				'inherit_id'  => 1,
				'name'      => "Proxy User",
				'capcode'   => NULL,
				'system'    => true,
			],
		]);
		
		$boardRoles = [];
		foreach (Board::get() as $board)
		{
			$boardRoles[] = [
				'role'       => "owner",
				'board_uri'  => $board->board_uri,
				'caste'      => NULL,
				'inherit_id' => 4,
				'name'       => "Board Owner",
				'capcode'    => "Board Owner",
				'system'     => false,
			];
		}
		
		DB::table('roles')->insert($boardRoles);
	}
}


use App\UserRole;

class UserRoleSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Associating roles ...');
		
		$userRoles = [
			[
				'user_id'  => 1,
				'role_id'  => 2,
			]
		];
		foreach (Board::get() as $board)
		{
			$userRoles[] = [
				'user_id' => $board->operated_by,
				'role_id' => $board->getOwnerRole()->role_id,
			];
		}
		DB::table('user_roles')->insert($userRoles);
	}
}


use App\RolePermission;

class RolePermissionSeeder extends Seeder {
	
	public function run()
	{
		// Insert default permissions.
		$defaultPermissions = [
			['role_id' => 1, 'value' => 1, 'permission_id' => "board.image.delete.self"],
			['role_id' => 1, 'value' => 1, 'permission_id' => "board.image.spoiler.upload"],
			['role_id' => 1, 'value' => 1, 'permission_id' => "board.post.delete.self"],
			
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.post.delete.other",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.post.delete.self",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.post.edit.other",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.post.edit.self",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.post.sticky",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.image.ban",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.image.delete.other",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.image.delete.self",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.image.spoiler.other",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.image.spoiler.upload",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.user.ban.reason",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.user.ban.free",],
			['role_id' => 3, 'value' => 1, 'permission_id' => "board.user.unban",],
		];
		
		$permissions = Permission::get();
		
		if (count($permissions))
		{
			foreach ($permissions as $permission)
			{
				$defaultPermissions[] = [
					'role_id'       => 2,
					'permission_id' => $permission->permission_id,
					'value'         => 1,
				];
			}
		}
		
		DB::table('role_permissions')->insert($defaultPermissions);
	}
}