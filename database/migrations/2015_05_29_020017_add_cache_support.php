<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCacheSupport extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('cache', function($table)
		{
			$table->string('key')->unique();
			$table->text('value');
			$table->integer('expiration');
		});
		
		Schema::drop('user_permission_cache');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('cache');
		
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

}
