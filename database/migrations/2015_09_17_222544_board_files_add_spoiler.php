<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BoardFilesAddSpoiler extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('file_attachments', function(Blueprint $table)
		{
			$table->boolean('is_spoiler')->default(false);
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('file_attachments', function(Blueprint $table)
		{
			$table->dropColumn('is_spoiler');
		});
	}
	
}
