<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PermissionGroups extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('permission_groups', function(Blueprint $table)
		{
			$table->increments('permission_group_id');
			$table->string('group_name');
			$table->integer('display_order')->unsigned();
			$table->boolean('is_account_only')->default(false);
			$table->boolean('is_system_only')->default(false);
			
			$table->unique('group_name');
		});
		
		Schema::create('permission_group_assignments', function(Blueprint $table)
		{
			$table->string('permission_id');
			$table->integer('permission_group_id')->unsigned();
			$table->integer('display_order')->unsigned();
			
			// Okay, so this isn't possible.
			//
			// [PDOException]
			// SQLSTATE[42000]: Syntax error or access violation:
			// 1059 Identifier name 'permission_group_assignments_permission_id_permission_group_id_unique' is too long
			//
			// $table->unique(['permission_id', 'permission_group_id']);
			
			// Foreigns and Indexes
			$table->foreign('permission_id')
				->references('permission_id')->on('permissions')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('permission_group_id')
				->references('permission_group_id')->on('permission_groups')
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
		Schema::drop('permission_groups');
		Schema::drop('permission_group_assignments');
	}

}
