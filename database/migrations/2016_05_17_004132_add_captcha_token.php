<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCaptchaToken extends Migration
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
			$table->dropColumn('client_ip');
		});

		Schema::table('captcha', function(Blueprint $table)
		{
			$table->ipAddress('client_ip')->after('hash')->nullable();
			$table->binary('client_session_id')->after('client_ip')->nullable();

			if (!(DB::connection() instanceof \Illuminate\Database\MySqlConnection))
			{
				$table->index('client_ip');
				$table->index('client_session_id');
			}
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
			$table->dropColumn('client_ip');
			$table->dropColumn('client_session_id');
			$table->ipAddress('client_ip')->after('hash')->nullable();
		});
	}
}
