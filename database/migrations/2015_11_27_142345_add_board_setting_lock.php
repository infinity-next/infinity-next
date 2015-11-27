<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBoardSettingLock extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('board_settings', function(Blueprint $table)
		{
			$table->boolean('is_locked')->default(false);
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('board_settings', function(Blueprint $table)
		{
			$table->dropColumn('is_locked');
		});
	}
	
}
