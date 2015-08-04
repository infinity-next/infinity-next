<?php

use App\Post;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PostAuthorTweaks extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('posts', function(Blueprint $table)
		{
			$table->string('author_ip', 46)->nullable()->change();
			$table->string('author_id', 6)->nullable()->after('author_ip');
			$table->timestamp('author_ip_nulled_at')->nullable()->after('author_id');
		});
		
		Post::withTrashed()->chunk(100, function($posts)
		{
			foreach ($posts as $post)
			{
				$post->author_id = $post->makeAuthorId();
				$post->save();
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
			$table->string('author_ip', 46)->change();
			$table->dropColumn('author_id', 'author_ip_nulled_at');
		});
	}
	
}
