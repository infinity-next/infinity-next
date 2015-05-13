<?php

use App\Board;
use App\Role;
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
			$table->integer('user')->unsigned();
			$table->integer('role')->unsigned();
			$table->binary('cache')->nullable()->default(NULL);
			
			$table->primary(['user', 'role']);
			$table->index('user');
			$table->index('role');
			
			$table->foreign('user')
				->references('id')->on('users')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('role')
				->references('id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		Schema::create('role_permissions', function(Blueprint $table)
		{
			$table->integer('role')->unsigned();
			$table->string('permission');
			$table->boolean('value');
			
			$table->primary(['role', 'permission']);
			$table->index('role');
			$table->index('permission');
			
			$table->foreign('role')
				->references('id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
				
			$table->foreign('permission')
				->references('permission')->on('permissions')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		
		$userRoles = [
			[
				'user'  => 1,
				'role'  => 2,
				'cache' => NULL,
			]
		];
		foreach (Board::get() as $board)
		{
			$userRoles[] =  [
				'user'  => $board->operated_by,
				'role'  => $board->getOwnerRole()->id,
				'cache' => NULL,
			];
		}
		DB::table('user_roles')->insert($userRoles);
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
