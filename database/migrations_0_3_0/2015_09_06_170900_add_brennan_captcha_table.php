<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBrennanCaptchaTable extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('captcha', function(Blueprint $table)
		{
			$table->increments('captcha_id');
			$table->binary('hash');
			$table->binary('client_ip');
			$table->string('solution');
			$table->timestamp('created_at');
			$table->timestamp('cracked_at')->nullable()->default(NULL);
			
			$table->unique('captcha_id');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('captcha');
	}
	
}
