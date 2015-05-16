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
			$table->increments('role_id');
			$table->string('role');
			$table->string('board_uri', 32)->nullable()->default(NULL);
			$table->string('caste')->nullable()->default(NULL);
			
			$table->integer('inherit_id')->unsigned()->nullable()->default(NULL);
			
			$table->string('name');
			$table->string('capcode')->nullable();
			
			$table->boolean('system')->default(false);
			
			// Foreigns and Indexes
			$table->index(['role', 'board_uri', 'caste']);
			
			$table->foreign('board_uri')
				->references('board_uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('inherit_id')
				->references('role_id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		Schema::table('posts', function(Blueprint $table)
		{
			$table->foreign('capcode_id')
				->references('role_id')->on('roles')
				->onDelete('set null')->onUpdate('cascade');
		});
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
