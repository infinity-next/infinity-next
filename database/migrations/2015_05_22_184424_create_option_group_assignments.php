<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionGroupAssignments extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('option_group_assignments', function(Blueprint $table)
		{
			$table->string('option_name');
			$table->integer('option_group_id')->unsigned();
			$table->integer('display_order')->unsigned();
			
			$table->unique(['option_name', 'option_group_id']);
			
			// Foreigns and Indexes
			$table->foreign('option_name')
				->references('option_name')->on('options')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('option_group_id')
				->references('option_group_id')->on('option_groups')
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
		Schema::drop('option_group_assignments');
	}
	
}