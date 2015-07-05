<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BoardFlags extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('boards', function(Blueprint $table)
		{
			$table->boolean('is_indexed')->default(true);
			$table->boolean('is_worksafe')->default(false);
			$table->boolean('is_overboard')->default(true);
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('boards', function(Blueprint $table)
		{
			$table->dropColumn('is_indexed');
			$table->dropColumn('is_worksafe');
			$table->dropColumn('is_overboard');
		});
	}

}
