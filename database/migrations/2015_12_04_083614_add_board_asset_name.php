<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBoardAssetName extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('board_assets', function(Blueprint $table)
		{
			$table->text('asset_name')->nullable()->default(NULL)->after('asset_type');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('board_assets', function(Blueprint $table)
		{
			$table->dropColumn('asset_name');
		});
	}
	
}
