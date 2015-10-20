<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePublicLog extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('logs', function(Blueprint $table)
		{
			$table->increments('action_id');
			$table->string('action_name');
			$table->binary('action_details')->nullable()->default(null);
			$table->integer('user_id')->unsigned()->nullable()->default(null);
			$table->string('user_ip', 46);
			$table->string('board_uri', 32)->nullable()->default(null);
			$table->timestamps();
			
			
			// Foreigns and Indexes
			$table->foreign('user_id')
				->references('user_id')->on('users')
				->onDelete('cascade')->onUpdate('cascade');
			
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
		Schema::drop('logs');
	}

}
