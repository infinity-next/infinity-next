<?php

use Illuminate\Database\Seeder;

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
		$this->command->info("Seeding admin user.");
		
		if (User::count() === 0)
		{
			// Generate a password.
			$password = [];
			for ($i = 0; $i < 4; ++$i)
			{
				$password[] = static::$potential_pass_words[array_rand(static::$potential_pass_words)];
			}
			$password = implode($password, " ");
			
			// Create the user.
			$user = User::firstOrNew([
				'user_id'  => 1,
			]);
			
			$user->username = "Admin";
			$user->password = bcrypt($password);
			$user->save();
			
			$this->command->info("User \"Admin\" has been created with the following password:\n{$password}");
		}
		else
		{
			$this->command->info("Skipped. Users exist.");
		}
	}
}
