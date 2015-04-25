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
		
		$this->call('BoardSeeder');
	}

}

use App\Board;

class BoardSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info('Attempting to seed the /test/ first board.');
		
		$board = Board::firstOrCreate([
				'uri' => "test",
				'title' => "Test",
				'description' => "Discover software features on your own",
				'created_by' => 1,
				'operated_by' => 1,
			]);
		
		$this->command->info('Board exists now. Accessible at /test/.');
	}
}