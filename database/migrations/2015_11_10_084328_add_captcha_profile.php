<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCaptchaProfile extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('captcha', function(Blueprint $table)
		{
			$table->string('profile', 64)->default("default")->after('solution');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('captcha', function(Blueprint $table)
		{
			$table->dropColumn('profile');
		});
	}
	
}
