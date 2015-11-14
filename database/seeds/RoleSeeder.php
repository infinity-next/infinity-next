<?php

use Illuminate\Database\Seeder;

use App\Board;
use App\Permission;
use App\Role;
use App\RolePermission;
use App\User;
use App\UserRole;

class RoleSeeder extends Seeder {
	
	public function run()
	{
		// $this->command->info('Seeding roles.');
		// 
		// $this->runMaster();
		// $this->runBoards();
	}
	
	public function runMaster()
	{
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
		foreach (Board::get() as $board)
		{
			$boardRole = $this->slugs()[Role::ID_OWNER];
			
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
		
		foreach (Board::get() as $board)
		{
			$ownerRole = $board->getOwnerRole();
			
			if ($ownerRole)
			{
				$userRoles[] = [
					'user_id' => $board->operated_by,
					'role_id' => $ownerRole->role_id,
				];
			}
			else
			{
				$this->command->line("\t/{$board->board_uri}/ has no owner role.");
			}
		}
		
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
				
				"board.reports",
				"site.reports",
				"site.board.view_unindexed",
			],
		];
	}
}
