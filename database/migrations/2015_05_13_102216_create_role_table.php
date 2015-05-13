<?php

use App\Board;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoleTable extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('roles', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('role');
			$table->string('board', 32)->nullable()->default(NULL);
			$table->string('caste')->nullable()->default(NULL);
			
			$table->integer('inherits')->unsigned()->nullable()->default(NULL);
			
			$table->string('name');
			$table->string('capcode')->nullable();
			
			$table->boolean('system')->default(false);
			
			
			$table->index(['role', 'board', 'caste']);
			
			$table->foreign('board')
				->references('uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('inherits')
				->references('id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		DB::table('roles')->insert([
			[
				'id'       => 1,
				'role'     => "anonymous",
				'board'    => NULL,
				'caste'    => NULL,
				'inherits' => NULL,
				'name'     => "Anonymous",
				'capcode'  => NULL,
				'system'   => true,
			],
			[
				'id'       => 2,
				'role'     => "admin",
				'board'    => NULL,
				'caste'    => NULL,
				'inherits' => NULL,
				'name'     => "Administrator",
				'capcode'  => "Administrator",
				'system'   => true,
			],
			[
				'id'       => 3,
				'role'     => "moderator",
				'board'    => NULL,
				'caste'    => NULL,
				'inherits' => NULL,
				'name'     => "Global Volunteer",
				'capcode'  => "Global Volunteer",
				'system'   => true,
			],
			[
				'id'       => 4,
				'role'     => "owner",
				'board'    => NULL,
				'caste'    => NULL,
				'inherits' => NULL,
				'name'     => "Board Owner",
				'capcode'  => "Board Owner",
				'system'   => true,
			],
			[
				'id'       => 5,
				'role'     => "volunteer",
				'board'    => NULL,
				'caste'    => NULL,
				'inherits' => NULL,
				'name'     => "Board Volunteer",
				'capcode'  => "Board Volunteer",
				'system'   => true,
			],
		]);
		
		$boardRoles = [];
		foreach (Board::get() as $board)
		{
			$boardRoles[] =  [
				'role'     => "owner",
				'board'    => $board->uri,
				'caste'    => NULL,
				'inherits' => 4,
				'name'     => "Board Owner",
				'capcode'  => "Board Owner",
				'system'   => false,
			];
		}
		
		DB::table('roles')->insert($boardRoles);
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('roles');
	}
	
}
