<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoardTags extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::dropIfExists('board_tags');
		
		Schema::create('board_tags', function(Blueprint $table)
		{
			$table->increments('board_tag_id');
			$table->string('board_uri', 255);
			$table->string('tag', 32);
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::dropIfExists('board_tags');
	}
}
