<?php

use App\Post;
use App\PostCite;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostReplies extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('post_cites', function(Blueprint $table)
		{
			$table->bigIncrements('post_cite_id');
			$table->bigInteger('post_id')->unsigned();
			$table->string('post_board_uri', 32);
			$table->bigInteger('post_board_id')->unsigned();
			$table->bigInteger('cite_id')->unsigned()->nullable();
			$table->string('cite_board_uri', 32);
			$table->bigInteger('cite_board_id')->unsigned()->nullable();
			
			$table->foreign('post_id')
				->references('post_id')->on('posts')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('post_board_uri')
				->references('board_uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
				
			$table->foreign('cite_id')
				->references('post_id')->on('posts')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('cite_board_uri')
				->references('board_uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		
		// Process citations.
		$posts = Post::withTrashed()->chunk(100, function($posts)
		{
			echo " - Adding citations for 100 posts.\n";
			
			foreach ($posts as $post)
			{
				$cited = $post->getCitesFromText();
				$cites = [];
				
				foreach ($cited['posts'] as $citedPost)
				{
					$cites[] = new PostCite([
						'post_board_uri' => $post->board_uri,
						'post_board_id'  => $post->board_id,
						'cite_id'        => $citedPost->post_id,
						'cite_board_uri' => $citedPost->board_uri,
						'cite_board_id'  => $citedPost->board_id,
					]);
				}
				
				foreach ($cited['boards'] as $citedBoard)
				{
					$cites[] = new PostCite([
						'post_board_uri' => $post->board_uri,
						'cite_board_uri' => $citedPost->board_uri,
					]);
				}
				
				if (count($cites) > 0)
				{
					$post->cites()->saveMany($cites);
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
		Schema::drop('post_cites');
	}

}
