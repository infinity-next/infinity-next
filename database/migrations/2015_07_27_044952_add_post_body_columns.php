<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostBodyColumns extends Migration
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
			$table->text('body_parsed')->nullable()->after('body');
			$table->timestamp('body_parsed_at')->nullable()->after('body_parsed');
			$table->text('body_html')->nullable()->after('body_parsed_at');
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
			$table->dropColumn('body_parsed');
			$table->dropColumn('body_parsed_at');
			$table->dropColumn('body_html');
		});
	}
	
}
