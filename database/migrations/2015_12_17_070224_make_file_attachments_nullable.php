<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeFileAttachmentsNullable extends Migration
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
			$table->bigInteger('file_id')->unsigned()->nullable()->change();
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
			$table->bigInteger('file_id')->unsigned()->change();
		});
	}
	
}
