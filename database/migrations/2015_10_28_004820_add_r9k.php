<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddR9k extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('bans', function(Blueprint $table)
		{
			$table->boolean('is_robot')->default(false);
		});
		
		Schema::create('post_checksums', function(Blueprint $table)
		{
			$table->bigIncrements('post_checksum_id');
			$table->string('board_uri', 32);
			$table->binary('checksum');
			
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
		Schema::table('bans', function(Blueprint $table)
		{
			$table->dropColumn('is_robot');
		});
		
		Schema::drop('post_checksums');
	}
}
