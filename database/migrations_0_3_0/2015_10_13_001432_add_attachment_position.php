<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAttachmentPosition extends Migration
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
			$table->smallInteger('position')->unsigned()->default(0);
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
			$table->dropColumn('position');
		});
	}
}
