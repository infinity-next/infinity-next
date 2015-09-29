<?php

use Illuminate\Database\Seeder;

use App\Board;

class BoardSeeder extends Seeder {
	
	public function run()
	{
		$this->command->info("Seeding boards.");
		
		if (Board::count() < 1)
		{
			$board = Board::firstOrCreate([
				'board_uri'   => "test",
				'title'       => "Test",
				'description' => "Discover software features on your own",
				'created_by'  => 1,
				'operated_by' => 1,
			]);
			
			$this->command->info("Success. At least one board exists now. Accessible at /test/.");
		}
		else
		{
			$this->command->info("Skipped. Site has at least one board.");
		}
	}
}
