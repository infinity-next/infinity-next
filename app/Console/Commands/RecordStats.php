<?php namespace App\Console\Commands;

use App\Board;

use Carbon\Carbon;
use Illuminate\Console\Command;

use Cache;
use Settings;

class RecordStats extends Command {
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'recordstats';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Record board statistics';
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// Generate an activity snapshot.
		Board::createStatsSnapshots();
		// Drop boardlist cache.
		Cache::forget('site.boardlist');
		// Generate boardlist again.
		Board::getBoardsForBoardlist();
	}
}