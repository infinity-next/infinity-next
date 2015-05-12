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
			'id'       => 1,
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
				'uri'         => "test",
				'title'       => "Test",
				'description' => "Discover software features on your own",
				'created_by'  => 1,
				'operated_by' => 1,
			]);
		
		$this->command->info("Board exists now. Accessible at /test/.");
	}
}