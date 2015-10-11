<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileTable extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('files', function(Blueprint $table)
		{
			$table->bigIncrements('file_id');
			$table->char('hash', 32);
			$table->boolean('banned')->default(false);
			$table->integer('filesize')->unsigned();
			$table->string('mime');
			$table->dateTime('first_uploaded_at');
			$table->dateTime('last_uploaded_at');
			$table->integer('upload_count')->unsigned();
			
			
			$table->unique('hash');
		});
		
		Schema::create('file_attachments', function(Blueprint $table)
		{
			$table->bigIncrements('attachment_id');
			$table->bigInteger('post_id')->unsigned();
			$table->bigInteger('file_id')->unsigned();
			
			$table->string('filename', 255);
			
			
			// Foreigns and Indexes
			$table->foreign('file_id')
				->references('file_id')->on('files')
				->onUpdate('cascade');
			
			$table->foreign('post_id')
				->references('post_id')->on('posts')
				->onDelete('cascade')->onUpdate('cascade');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('files');
		Schema::drop('file_attachments');
	}
}