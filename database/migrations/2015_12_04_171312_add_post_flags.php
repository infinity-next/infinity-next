<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostFlags extends Migration
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
			$table->integer('flag_id')->unsigned()->nullable()->default(NULL)->after('password');
			
			$table->foreign('flag_id')
				->references('board_asset_id')->on('board_assets')
				->onDelete('set null')->onUpdate('cascade');
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
			$table->dropColumn('flag_id');
		});
	}
	
}
