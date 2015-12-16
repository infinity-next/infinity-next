<?php

use Illuminate\Database\Seeder as Seeder;
use Illuminate\Database\Eloquent\Model as Model;

if (!class_exists("DatabaseSeeder"))
{
	class DatabaseSeeder extends Seeder {
		
		/**
		 * Run the database seeds.
		 *
		 * @return void
		 */
		public function run()
		{
			Model::unguard();
			
			// Due to bizarre and confusing reasons,
			// I am forced to require these classes like this.
			require_once(__DIR__.'/BoardSeeder.php');
			require_once(__DIR__.'/OptionSeeder.php');
			require_once(__DIR__.'/PermissionSeeder.php');
			require_once(__DIR__.'/RoleSeeder.php');
			require_once(__DIR__.'/UserSeeder.php');
			
			$this->call('UserSeeder');
			$this->call('BoardSeeder');
			
			$this->call('RoleSeeder');
			$this->call('UserRoleSeeder');
			
			$this->call('PermissionSeeder');
			$this->call('PermissionGroupSeeder');
			$this->call('RolePermissionSeeder');
			
			$this->call('OptionSeeder');
			$this->call('OptionGroupSeeder');
			
			echo \Artisan::call('cache:clear');
		}
	}
}
