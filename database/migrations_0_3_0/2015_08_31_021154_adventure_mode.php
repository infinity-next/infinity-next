<?php

use App\Board;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AdventureMode extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('board_adventures', function(Blueprint $table)
		{
			$table->increments('adventure_id');
			$table->binary('adventurer_ip');
			$table->string('board_uri', 32);
			$table->integer('post_id')->unsigned()->nullable()->default(NULL);
			$table->timestamps();
			$table->timestamp('expires_at');
			$table->timestamp('expended_at')->nullable()->default(NULL);
			
			$table->foreign('board_uri')
				->references('board_uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		Schema::table('posts', function(Blueprint $table)
		{
			$table->integer('adventure_id')->unsigned()->nullable()->default(NULL)->after('locked_at');
			
			$table->foreign('adventure_id')
				->references('adventure_id')->on('board_adventures')
				->onDelete('set null')->onUpdate('cascade');
		});
		
		Schema::table('boards', function(Blueprint $table)
		{
			$table->timestamp('last_post_at')->nullable()->default(NULL)->after('updated_at');
		});
		
		Board::chunk(100, function($boards)
		{
			foreach ($boards as $board)
			{
				$lastPost = $board->posts()
					->orderBy('created_at', 'desc')
					->limit(1)
					->get()
					->last();
				
				if ($lastPost)
				{
					$board->last_post_at = $lastPost->created_at;
					
					$board->save();
				}
			}
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('posts', function(Blueprint $table)
		{
			$table->dropForeign('posts_adventure_id_foreign');
			$table->dropColumn('adventure_id');
		});
		
		Schema::table('boards', function(Blueprint $table)
		{
			$table->dropColumn('last_post_at');
		});
		
		Schema::drop('board_adventures');
	}
	
}
