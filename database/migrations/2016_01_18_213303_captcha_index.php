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
			$table->inet('client_ip')->after('hash')->nullable();
			$table->index('client_ip');
			$table->index('hash');
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
			$table->dropIndex('captcha_client_ip_index');
			$table->dropIndex('captcha_hash_index');
			$table->dropColumn('client_ip');
			$table->binary('client_ip')->after('hash')->nullable();
		});
	}
	
}
