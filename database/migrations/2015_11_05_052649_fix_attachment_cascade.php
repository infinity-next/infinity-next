<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixAttachmentCascade extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		/**
		 * File Attachments Keys
		 */
		Schema::table('file_attachments', function(Blueprint $table)
		{
			$table->dropForeign('file_attachments_file_id_foreign');
			$table->dropForeign('file_attachments_post_id_foreign');
			
			// Foreigns and Indexes
			$table->foreign('file_id')
				->references('file_id')->on('files')
				->onUpdate('cascade');
			
			$table->foreign('post_id')
				->references('post_id')->on('posts')
				->onUpdate('cascade');
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
		 * File Attachments Keys
		 */
		Schema::table('file_attachments', function(Blueprint $table)
		{
			$table->dropForeign('file_attachments_file_id_foreign');
			$table->dropForeign('file_attachments_post_id_foreign');
			
			// Foreigns and Indexes
			$table->foreign('file_id')
				->references('file_id')->on('files')
				->onUpdate('cascade');
			
			$table->foreign('post_id')
				->references('post_id')->on('posts')
				->onDelete('cascade')->onUpdate('cascade');
		});
	}
}
