<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStripeChargeTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('payments', function(Blueprint $table)
		{
			// Bulk data
			$table->increments('id');
			$table->integer('customer')->unsigned()->nullable()->default(NULL);
			$table->string('attribution', 255)->unsigned()->nullable()->default(NULL);
			$table->string('ip', 46);
			$table->timestamp('created_at');
			$table->integer('amount');
			$table->string('currency', 3);
			$table->string('subscription', 64)->nullable();
			
			// Keys
			$table->foreign('customer')->references('id')->on('users');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('payments');
	}

}