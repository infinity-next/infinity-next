<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReportAddPromotions extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('reports', function(Blueprint $table)
		{
			$table->timestamp('promoted_at')->nullable();
			$table->integer('promoted_by')->nullable()->unsigned();
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('reports', function(Blueprint $table)
		{
			$table->dropColumn(
				'promoted_at',
				'promoted_by'
			);
		});
	}
}
