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
			$table->index('file_id');
			$table->index('post_id');
			$table->index('is_spoiler');
			$table->index('is_deleted');
		});
		
		Schema::table('files', function(Blueprint $table)
		{
			$table->index('banned');
			$table->index('has_thumbnail');
			$table->index('first_uploaded_at');
			$table->index('last_uploaded_at');
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
			$table->dropIndex('file_attachments_file_id_index');
			$table->dropIndex('file_attachments_post_id_index');
			$table->dropIndex('file_attachments_is_deleted_index');
			$table->dropIndex('file_attachments_is_spoiler_index');
		});
		
		Schema::table('files', function(Blueprint $table)
		{
			$table->dropIndex('files_banned_index');
			$table->dropIndex('files_has_thumbnail_index');
			$table->dropIndex('files_first_uploaded_at_index');
			$table->dropIndex('files_last_uploaded_at_index');
		});
	}
	
}
