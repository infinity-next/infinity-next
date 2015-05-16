<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRolePivots extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_roles', function(Blueprint $table)
		{
			$table->integer('user_id')->unsigned();
			$table->integer('role_id')->unsigned();
			
			$table->primary(['user_id', 'role_id']);
			$table->index('user_id');
			$table->index('role_id');
			
			$table->foreign('user_id')
				->references('user_id')->on('users')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('role_id')
				->references('role_id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		Schema::create('role_permissions', function(Blueprint $table)
		{
			$table->integer('role_id')->unsigned();
			$table->string('permission_id');
			$table->boolean('value');
			
			$table->primary(['role_id', 'permission_id']);
			$table->index('role_id');
			$table->index('permission_id');
			
			$table->foreign('role_id')
				->references('role_id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
				
			$table->foreign('permission_id')
				->references('permission_id')->on('permissions')
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
		Schema::drop('user_roles');
		Schema::drop('role_permissions');
	}
	
}
