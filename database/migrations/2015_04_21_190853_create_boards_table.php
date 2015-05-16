<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoardsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('boards', function(Blueprint $table)
		{
			$table->string('board_uri', 255)->unique();
			$table->string('title', 255);
			$table->string('description', 255)->default(NULL)->nullable();
			$table->timestamps();
			$table->integer('created_by')->unsigned();
			$table->integer('operated_by')->unsigned();
			$table->integer('posts_total')->unsigned()->default(0);
			
			// Foreigns and Indexes
			$table->primary('board_uri');
			
			$table->foreign('created_by')
				->references('user_id')->on('users')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('operated_by')
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
		Schema::drop('boards');
	}

}
