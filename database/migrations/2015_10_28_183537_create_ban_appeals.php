<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBanAppeals extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('ban_appeals', function(Blueprint $table)
		{
			$table->increments('ban_appeal_id');
			$table->timestamps();
			$table->integer('ban_id')->unsigned();
			$table->ipAddress('appeal_ip');
			$table->text('appeal_text');
			$table->boolean('seen')->default(false);
			$table->boolean('approved')->nullable()->default(null);
			$table->integer('mod_id')->unsigned()->nullable();

			$table->foreign('ban_id')
				->references('ban_id')->on('bans')
				->onDelete('cascade')->onUpdate('cascade');

			$table->foreign('mod_id')
				->references('user_id')->on('users')
				->onDelete('cascade')->onUpdate('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('ban_appeals');
	}
}
