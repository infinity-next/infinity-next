<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReports extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('reports', function(Blueprint $table)
		{
			$table->increments('report_id');
			$table->string('reason', 1024);
			$table->string('board_uri', 32);
			$table->bigInteger('post_id')->nullable()->unsigned();
			$table->string('ip', 46)->nullable();
			$table->integer('user_id')->nullable()->unsigned();
			$table->timestamps();
			
			
			// Relationshps
			$table->foreign('user_id')
				->references('user_id')->on('users')
				->onDelete('set null')->onUpdate('set null');
			
			$table->foreign('post_id')
				->references('post_id')->on('posts')
				->onDelete('set null')->onUpdate('set null');
			
			$table->foreign('board_uri')
				->references('board_uri')->on('boards')
				->onDelete('set null')->onUpdate('set null');
			
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}
	
}
