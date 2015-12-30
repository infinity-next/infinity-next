<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OptimizeAttachments extends Migration
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
			$table->index('is_spoiler');
			$table->index('is_deleted');
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
			$table->dropIndex('file_attachments_is_deleted_index');
			$table->dropIndex('file_attachments_is_spoiler_index');
		});
		
		Schema::table('files', function(Blueprint $table)
		{
			$table->dropIndex('files_has_thumbnail_index');
		});
	}
	
}
