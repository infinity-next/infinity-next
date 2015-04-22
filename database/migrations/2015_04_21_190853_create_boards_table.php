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
			$table->string('uri', 256)->unique();
			$table->string('title', 256);
			$table->string('description', 256)->default(NULL)->nullable();
			$table->timestamp('created_at');
			$table->integer('created_by')->unsigned();
			$table->integer('operated_by')->unsigned();
			
			$table->primary('uri');
			$table->foreign('created_by')->references('id')->on('users');
			$table->foreign('operated_by')->references('id')->on('users');
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
