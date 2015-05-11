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
			$table->bigIncrements('id');
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
			$table->bigIncrements('id');
			$table->bigInteger('post')->unsigned();
			$table->bigInteger('file')->unsigned();
			
			$table->string('filename', 255);
			
			
			$table->foreign('post')->references('id')->on('posts');
			$table->foreign('file')->references('id')->on('files');
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