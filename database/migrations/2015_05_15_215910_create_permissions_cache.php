<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionsCache extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_permission_cache', function(Blueprint $table)
		{
			$table->increments('permission_cache_id');
			$table->integer('user_id')->unsigned()->nullable()->default(null);
			$table->text('cache');
			
			$table->foreign('user_id')
				->references('user_id')->on('users')
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
		Schema::drop('user_permission_cache');
	}
	
}
