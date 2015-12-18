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
		Schema::table('pages', function(Blueprint $table)
		{
			$table->increments('page_id');
			$table->timestamps();
			$table->string('name', 128);
			$table->text('title')->nullable();
			$table->text('body')->nullable();
			$table->text('body_parsed')->nullable();
			$table->timestamp('body_parsed_at')->nullable();
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
