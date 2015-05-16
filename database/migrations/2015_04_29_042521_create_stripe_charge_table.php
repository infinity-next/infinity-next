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
			$table->increments('payment_id');
			$table->integer('customer_id')->unsigned()->nullable()->default(NULL);
			$table->string('attribution', 255)->nullable()->default(NULL);
			$table->string('ip', 46);
			$table->timestamp('created_at');
			$table->integer('amount');
			$table->string('currency', 3);
			$table->string('subscription', 64)->nullable();
			
			// Foreigns and Indexes
			$table->foreign('customer_id')
				->references('user_id')->on('users')
				->onUpdate('cascade');
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