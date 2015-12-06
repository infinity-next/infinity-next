<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostsFilesColumn extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/**
		 * Posts
		 */
		Schema::table('posts', function(Blueprint $table)
		{
			$table->integer('reply_file_count')->unsigned()->default(0)->after('reply_count');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		/**
		 * Posts
		 */
		Schema::table('posts', function(Blueprint $table)
		{
			$table->dropColumn('reply_file_count');
		});
	}
	
}
