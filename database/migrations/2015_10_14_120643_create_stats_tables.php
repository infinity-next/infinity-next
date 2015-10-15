<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Board;
use App\Post;
use App\Stats;
use APp\StatsUnique;

class CreateStatsTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('stats_uniques');
		Schema::dropIfExists('stats');
		
		Schema::create('stats', function(Blueprint $table)
		{
			$table->bigIncrements('stats_id');
			$table->string('board_uri', 32);
			$table->timestamp('stats_time');
			$table->string('stats_type', 25);
			$table->bigInteger('counter')->unsigned()->default(0);
			
			$table->unique(['stats_time', 'board_uri', 'stats_type']);
		});
		
		Schema::create('stats_uniques', function(Blueprint $table)
		{
			$table->bigIncrements('stats_bit_id');
			$table->bigInteger('stats_id')->unsigned();
			$table->bigInteger('unique');
			
			$table->foreign('stats_id')
				->references('stats_id')->on('stats')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		
		$firstPost = Post::orderBy('post_id', 'asc')->first()->pluck('created_at');
		$trackTime = clone $firstPost;
		$nowTime   = \Carbon\Carbon::now()->minute(0)->second(0)->timestamp;
		$boards    = Board::all();
		
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
				echo "\tAdded {$hourCount} new stat row(s) from " . $firstPost->diffForHumans() . "\n";
			}
		}
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('stats_uniques');
		Schema::drop('stats');
	}
}
