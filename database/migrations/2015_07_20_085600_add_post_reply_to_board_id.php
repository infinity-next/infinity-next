<?php

use App\Post;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostReplyToBoardId extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('posts', function(Blueprint $table)
		{
			$table->bigInteger('reply_to_board_id')->unsigned()->nullable()->after('reply_to');
		});
		
		// Add reply_to_board_id for all posts.
		$replies = Post::withTrashed()
			->whereNotNull('posts.reply_to')
			->leftJoin('posts as op', function($join)
			{
				$join->on('op.post_id', '=', 'posts.reply_to');
			})
			->addSelect(
				'posts.*',
				'op.board_id as op_board_id'
			)
			->get();
		
		foreach ($replies as $reply)
		{
			$reply->reply_to_board_id = $reply->op_board_id;
			$reply->save();
		}
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
			$table->dropColumn('reply_to_board_id');
		});
	}
	
}
