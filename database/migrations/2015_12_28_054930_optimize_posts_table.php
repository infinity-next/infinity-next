<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OptimizePostsTable extends Migration
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
			$table->boolean('body_has_content')->default(true)->after('body_too_long');
		});
		
		Schema::table('posts', function(Blueprint $table)
		{
			$table->index('bumped_last');
			$table->index('created_at');
			$table->index('deleted_at');
			$table->index('featured_at');
			$table->index('reply_to');
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
			$table->dropColumn('body_has_content');
			$table->dropIndex('posts_bumped_last_index');
			$table->dropIndex('posts_created_at_index');
			$table->dropIndex('posts_deleted_at_index');
			$table->dropIndex('posts_featured_at_index');
			$table->dropIndex('posts_reply_to_index');
		});
	}
	
}
