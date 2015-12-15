<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Schema\Blueprint;

use App\Board;
use App\Permission;
use App\Role;
use App\RolePermission;
use App\User;
use App\UserRole;

class RoleSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Seeding roles.');
		
		$this->sequencePrep();
		$this->runMaster();
		$this->sequenceCleanup();
		
		$this->runBoards();
		
		$deleted = UserRole::whereDoesntHave('role')->forceDelete();
		
		if ($deleted > 0)
		{
			$this->command->warn("Dropped {$deleted} user roles where the role did not exist.");
		}
		
		Schema::table('posts', function(Blueprint $table)
		{
			$table->foreign('capcode_id')
				->references('role_id')->on('roles')
				->onDelete('set null')->onUpdate('cascade');
		});
		Schema::table('role_permissions', function(Blueprint $table)
		{
			$table->foreign('role_id')
				->references('role_id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
		});
		Schema::table('user_roles', function(Blueprint $table)
		{
			$table->foreign('role_id')
				->references('role_id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
		});
	}
	
	public function sequencePrep()
	{
		$this->command->comment("\tPrepping database.");
		
		# Yes, we are required to drop and recreate these FKs to get ID reassignment working.
		# This is for PostgreSQL.
		try
		{
			Schema::table('posts', function(Blueprint $table)
			{
				$table->dropForeign('posts_capcode_id_foreign');
			});
			Schema::table('role_permissions', function(Blueprint $table)
			{
				$table->dropForeign('role_permissions_role_id_foreign');
			});
			Schema::table('user_roles', function(Blueprint $table)
			{
				$table->dropForeign('user_roles_role_id_foreign');
			});
		}
		catch (\Exception $e)
		{
			$this->command->comment("\tSkipping FK drops. Probably already missing.");
		}
		
		if (DB::connection() instanceof \Illuminate\Database\PostgresConnection)
		{
			DB::statement("DROP SEQUENCE IF EXISTS roles_role_id_seq CASCADE;");
		}
	}
	
	public function sequenceCleanup()
	{
		$this->command->comment("\tCleaning up relationships.");
		
		if (DB::connection() instanceof \Illuminate\Database\PostgresConnection)
		{
			$this->command->comment("\tDealing with PGSQL sequences.");
			DB::statement("CREATE SEQUENCE roles_role_id_seq OWNED BY \"roles\".\"role_id\";");
			DB::statement("SELECT setval('roles_role_id_seq', COALESCE((SELECT MAX(role_id)+1 FROM roles), 1), false);");
			DB::statement("ALTER TABLE roles ALTER COLUMN role_id SET DEFAULT nextval('roles_role_id_seq');");
		}
	}
	
	public function runMaster()
	{
		$this->command->comment("\tInserting system roles.");
		foreach ($this->slugs() as $slug)
		{
			$role = Role::where([
				'role'       => $slug['role'],
				'board_uri'  => $slug['board_uri'],
				'caste'      => $slug['caste'],
				'system'     => true,
			])->first();
			
			if ($role)
			{
				$role->forceDelete();
			}
			
			$role = new Role($slug);
			$role->role_id = $slug['role_id'];
			$role->save();
		}
	}
	
	public function runBoards()
	{
		$this->command->comment("\tInserting board owner roles.");
		$boardRole = $this->slugs()[Role::ID_OWNER];
		
		foreach (Board::get() as $board)
		{
			$roleModel = Role::updateOrCreate([
				'role'       => $boardRole['role'],
				'board_uri'  => $board->board_uri,
				'caste'      => $boardRole['caste'],
			], [
				'name'       => $boardRole['name'],
				'capcode'    => $boardRole['capcode'],
				'inherit_id' => Role::ID_OWNER,
				'system'     => false,
				'weight'     => $boardRole['weight'] + 5,
			]);
		}
	}
	
	private function slugs()
	{
		return [
			Role::ID_ANONYMOUS => [
				'role_id'    => Role::ID_ANONYMOUS,
				'role'       => "anonymous",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'name'       => "user.role.anonymous",
				'capcode'    => NULL,
				'inherit_id' => NULL,
				'system'     => true,
				'weight'     => Role::WEIGHT_ANONYMOUS,
			],
			Role::ID_UNACCOUNTABLE => [
				'role_id'    => Role::ID_UNACCOUNTABLE,
				'role'       => "unaccountable",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'name'       => "user.role.unaccountable",
				'capcode'    => NULL,
				'inherit_id' => NULL,
				'system'     => true,
				'weight'     => Role::WEIGHT_UNACCOUNTABLE,
			],
			Role::ID_REGISTERED => [
				'role_id'    => Role::ID_REGISTERED,
				'role'       => "registered",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'name'       => "user.role.registered",
				'capcode'    => NULL,
				'inherit_id' => NULL,
				'system'     => true,
				'weight'     => Role::WEIGHT_REGISTERED,
			],
			Role::ID_JANITOR => [
				'role_id'    => Role::ID_JANITOR,
				'role'       => "janitor",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'name'       => "user.role.board_mod",
				'capcode'    => "user.role.board_mod",
				'inherit_id' => NULL,
				'system'     => true,
				'weight'     => Role::WEIGHT_JANITOR,
			],
			Role::ID_OWNER => [
				'role_id'    => Role::ID_OWNER,
				'role'       => "owner",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'name'       => "user.role.board_owner",
				'capcode'    => "user.role.board_owner",
				'inherit_id' => NULL,
				'system'     => true,
				'weight'     => Role::WEIGHT_OWNER,
			],
			Role::ID_MODERATOR => [
				'role_id'    => Role::ID_MODERATOR,
				'role'       => "moderator",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'name'       => "user.role.global_mod",
				'capcode'    => "user.role.global_mod",
				'inherit_id' => NULL,
				'system'     => true,
				'weight'     => Role::WEIGHT_MODERATOR,
			],
			Role::ID_ADMIN => [
				'role_id'    => Role::ID_ADMIN,
				'role'       => "admin",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'name'       => "user.role.admin",
				'capcode'    => "user.role.admin",
				'inherit_id' => NULL,
				'system'     => true,
				'weight'     => Role::WEIGHT_ADMIN,
			],
			Role::ID_ABSOLUTE => [
				'role_id'    => Role::ID_ABSOLUTE,
				'role'       => "absolute",
				'board_uri'  => NULL,
				'caste'      => NULL,
				'name'       => "user.role.absolute",
				'capcode'    => NULL,
				'inherit_id' => NULL,
				'system'     => true,
				'weight'     => ROLE::WEIGHT_ABSOLUTE,
			],
		];
	}
}


class UserRoleSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Seeding role to user associations.');
		$userRoles = [];
		
		$admins = User::whereIn('user_id', explode(",", env('APP_ROOT_USERS', "1")))->get();
		
		foreach ($admins as $admin)
		{
			$userRoles[] = [
				'user_id'  => $admin->user_id,
				'role_id'  => Role::ID_ADMIN,
			];
		}
		
		Board::with('operator', 'roles')->has('operator')->chunk(50, function($boards) use (&$userRoles)
		{
			foreach ($boards as $board)
			{
				$ownerRole = $board->getOwnerRole();
				
				if (!$ownerRole)
				{
					$ownerRole = Role::getOwnerRoleForBoard($board);
					$this->command->line("\t/{$board->board_uri}/ has no owner role.");
				}
				
				$userRoles[] = [
					'user_id' => $board->operated_by,
					'role_id' => $ownerRole->role_id,
				];
			}
		});
		
		foreach ($userRoles as $userRole)
		{
			UserRole::firstOrCreate($userRole);
		}
	}
}


class RolePermissionSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Seeding permission to role associations.');
		
		RolePermission::truncate();
		
		$permissions = Permission::get()->modelKeys();
		
		// Insert default permissions.
		foreach ($this->slugs() as $role_id => $slugs)
		{
			foreach($slugs as $slug_key => $slug_value)
			{
				if (!is_numeric($slug_key) && (is_numeric($slug_value) || is_bool($slug_value)))
				{
					$permission_id    = $slug_key;
					$permission_value = !!$slug_value;
				}
				else
				{
					$permission_id    = $slug_value;
					$permission_value = true;
				}
				
				if (in_array($permission_id, $permissions))
				{
					(new RolePermission([
						'role_id'       => $role_id,
						'permission_id' => $permission_id,
						'value'         => $permission_value,
					]))->save();
					
				}
				else
				{
					$this->command->error("Attempting to assign non-existant permission id `{$permission_id}` to role_id `{$role_id}`.");
				}
			}
		}
		
		
		// Give admin permissions.
		if (count($permissions))
		{
			$role = Role::find( Role::ID_ADMIN );
			$role->permissions()->detach();
			
			$attachments = [];
			
			foreach ($permissions as $permission_id)
			{
				$attachments[] =[
					'permission_id' => $permission_id,
					'value'         => 1,
				];
			}
			
			$role->permissions()->attach($attachments);
		}
	}
	
	private function slugs()
	{
		return [
			Role::ID_ANONYMOUS => [
				"board.create",
				"board.logs",
				
				"board.image.upload.new",
				"board.image.upload.old",
				"board.image.delete.self",
				"board.image.spoiler.upload",
				"board.post.create.thread",
				"board.post.create.reply",
				"board.post.delete.self",
				"board.post.report",
				
				"site.post.report",
				"site.user.create",
			],
			
			Role::ID_UNACCOUNTABLE => [
				"board.image.upload.new" => false,
			],
			
			Role::ID_REGISTERED => [
				"site.pm",
				"site.profile.edit.other",
				"site.profile.edit.self",
				"site.profile.view",
				"site.user.merge",
			],
			
			Role::ID_JANITOR => [
				"board.logs",
				"board.history",
				"board.reports",
				"board.image.upload.new",
				"board.image.upload.old",
				"board.image.delete.self",
				"board.image.delete.other",
				"board.image.spoiler.upload",
				"board.image.spoiler.other",
				"board.post.create.thread",
				"board.post.create.reply",
				"board.post.delete.self",
				"board.post.delete.other",
				"board.post.edit.self",
				"board.post.edit.other",
				"board.post.sticky",
				"board.post.lock",
				"board.post.bumplock",
				"board.post.lock_bypass",
				"board.post.report",
				"board.user.ban.reason",
			],
			
			Role::ID_OWNER => [
				"board.config",
				"board.logs",
				"board.history",
				"board.reassign",
				"board.reports",
				"board.image.upload.new",
				"board.image.upload.old",
				"board.image.delete.self",
				"board.image.delete.other",
				"board.image.spoiler.upload",
				"board.image.spoiler.other",
				"board.post.create.thread",
				"board.post.create.reply",
				"board.post.delete.self",
				"board.post.delete.other",
				"board.post.edit.self",
				"board.post.edit.other",
				"board.post.sticky",
				"board.post.lock",
				"board.post.bumplock",
				"board.post.lock_bypass",
				"board.post.report",
				"board.user.ban.reason",
				"board.user.ban.free",
				"board.user.role",
				"board.user.unban",
			],
			
			Role::ID_MODERATOR => [
				"board.logs",
				"board.image.upload.new",
				"board.image.upload.old",
				"board.image.delete.self",
				"board.image.delete.other",
				"board.image.spoiler.upload",
				"board.image.spoiler.other",
				"board.post.create.thread",
				"board.post.create.reply",
				"board.post.delete.self",
				"board.post.delete.other",
				"board.post.edit.self",
				"board.post.edit.other",
				"board.post.sticky",
				"board.post.lock",
				"board.post.bumplock",
				"board.post.lock_bypass",
				"board.post.report",
				"board.user.ban.reason",
				"board.user.ban.free",
				"board.user.role",
				"board.user.unban",
				
				"board.history",
				"board.reports",
				"site.reports",
				"site.board.view_unindexed",
			],
		];
	}
}
