<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PostTableUpgrade extends Migration
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
			$table->string('author_ip', 46)->nullable()->change();
			$table->timestamp('author_ip_nulled_at')->nullable()->after('author_ip');
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
			$table->string('author_ip', 46)->change();
			$table->dropColumn('author_ip_nulled_at');
		});
	}
	
}
