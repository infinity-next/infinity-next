<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePages extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('pages');
		Schema::create('pages', function(Blueprint $table)
		{
			$table->increments('page_id');
			$table->timestamps();
			$table->string('board_uri', 32)->nullable()->default(null);
			$table->string('name', 128);
			$table->text('title')->nullable();
			$table->text('body')->nullable();
			$table->text('body_parsed')->nullable();
			$table->timestamp('body_parsed_at')->nullable();
			
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
		Schema::drop('pages');
	}
	
}
