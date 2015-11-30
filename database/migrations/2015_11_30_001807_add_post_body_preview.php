<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostBodyPreview extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('posts', function(Blueprint $table)
		{
			$table->boolean('body_too_long')->nullable()->after('body');
			$table->text('body_parsed_preview')->nullable()->after('body_parsed');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('posts', function(Blueprint $table)
		{
			$table->dropColumn('body_too_long');
			$table->dropColumn('body_parsed_preview');
		});
	}
	
}
