<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionCache extends Migration
{
	/**
	* Run the migrations.
	*
	* @return void
	*/
	public function up()
	{
		Schema::create('role_cache', function(Blueprint $table)
		{
			$table->increments('role_cache_id');
			$table->integer('user_id')->unsigned()->nullable();
			$table->string('board_uri', 32)->nullable();
			
			// Foreigns and Indexes
			$table->index(['user_id', 'board_uri']);
			
			$table->foreign('user_id')
				->references('user_id')->on('users')
				->onDelete('cascade')->onUpdate('cascade');
			
			// Foreigns and Indexes
			$table->foreign('board_uri')
				->references('board_uri')->on('boards')
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
		Schema::drop('role_cache');
	}
	
}
