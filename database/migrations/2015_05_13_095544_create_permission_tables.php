<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissionTables extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permissions', function(Blueprint $table)
		{
			$table->string('permission_id');
			$table->boolean('base_value')->default(0);
			
			$table->primary('permission_id');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('permissions');
	}
	
}
