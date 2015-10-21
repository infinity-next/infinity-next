<?php namespace App\Console\Commands;

use App\Board;
use App\Post;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RecordStatsAll extends Command
{
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'recordstats:all';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '(Slowly) record board statistics for all existing data.';
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$firstPost = Post::orderBy('post_id', 'asc')->first()->pluck('created_at');
		$trackTime = clone $firstPost;
		$nowTime   = Carbon::now()->minute(0)->second(0)->timestamp;
		$boards    = Board::all();
		
		$this->comment("Reviewing all records.");
		
		while ($firstPost->timestamp < $nowTime)
		{
			$firstPost = $firstPost->addHour();
			$hourCount = 0;
			
			foreach ($boards as $board)
			{
				if ($board->posts_total > 0)
				{
					$newRows = $board->createStatsSnapshot($firstPost);
					$hourCount += count($newRows);
				}
			}
			
			if ($hourCount > 0)
			{
				$this->comment("\tAdded {$hourCount} new stat row(s) from " . $firstPost->diffForHumans());
			}
		}
	}
	
}
