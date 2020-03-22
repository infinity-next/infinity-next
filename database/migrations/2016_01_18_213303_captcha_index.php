<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CaptchaIndex extends Migration
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
			if (!(DB::connection() instanceof \Illuminate\Database\MySqlConnection))
			{
				$table->index('client_ip');
				$table->index('hash');
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
			if (!(DB::connection() instanceof \Illuminate\Database\MySqlConnection))
			{
				$table->dropIndex('captcha_client_ip_index');
				$table->dropIndex('captcha_hash_index');
			}
			$table->dropColumn('client_ip');
			$table->ipAddress('client_ip')->after('hash')->nullable();
		});
	}

}
