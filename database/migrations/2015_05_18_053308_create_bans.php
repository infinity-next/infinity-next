<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBans extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ban_reasons', function(Blueprint $table)
		{
			$table->increments('ban_reason_id');
			$table->timestamps();
			$table->string('board_uri', 32)->nullable()->default(null);
			$table->string('ban_name');
			$table->string('ban_text');
			$table->string('mod_tip')->nullable()->default(null);
			$table->boolean('require_file')->default(false);
			$table->boolean('require_text')->default(false);
			$table->boolean('delete_file')->default(false);
			$table->boolean('delete_text')->default(false);
			
			// Foreigns and Indexes
			$table->foreign('board_uri')
				->references('board_uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
			
		});
		
		Schema::create('bans', function(Blueprint $table)
		{
			$table->increments('ban_id');
			$table->string('ban_ip', 46);
			$table->boolean('seen')->default(false);
			$table->timestamps();
			$table->timestamp('expires_at')->nullable()->default(null);
			
			$table->string('board_uri', 32)->nullable()->default(null);
			$table->integer('mod_id')->unsigned()->nullable()->default(null);
			$table->bigInteger('post_id')->unsigned()->nullable()->default(null);
			$table->integer('ban_reason_id')->unsigned()->nullable()->default(null);
			$table->string('justification')->default(null);
			
			// Foreigns and Indexes
			$table->foreign('board_uri')
				->references('board_uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('mod_id')
				->references('user_id')->on('users')
				->onDelete('set null')->onUpdate('cascade');
			
			$table->foreign('post_id')
				->references('post_id')->on('posts')
				->onDelete('set null')->onUpdate('cascade');
			
			$table->foreign('ban_reason_id')
				->references('ban_reason_id')->on('ban_reasons')
				->onDelete('set null')->onUpdate('cascade');
			
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('bans');
		Schema::drop('ban_reasons');
	}
	
}
