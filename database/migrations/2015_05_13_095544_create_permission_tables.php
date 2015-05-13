<?php

use App\Board;
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
			$table->string('permission');
			$table->boolean('base_value')->default(0);
			
			$table->primary('permission');
		});
		
		DB::table('permissions')->insert([
			[
				'permission' => "board.create",
				'base_value' => 0,
			],
			[
				'permission' => "board.delete",
				'base_value' => 0,
			],
			[
				'permission' => "board.reassign",
				'base_value' => 0,
			],
			[
				'permission' => "board.post.delete.self",
				'base_value' => 1,
			],
			[
				'permission' => "board.post.delete.other",
				'base_value' => 0,
			],
			[
				'permission' => "board.post.edit.self",
				'base_value' => 0,
			],
			[
				'permission' => "board.post.edit.other",
				'base_value' => 0,
			],
			[
				'permission' => "board.post.sticky",
				'base_value' => 0,
			],
			[
				'permission' => "board.post.ban",
				'base_value' => 0,
			],
			[
				'permission' => "board.image.ban",
				'base_value' => 0,
			],
			[
				'permission' => "board.image.delete.self",
				'base_value' => 0,
			],
			[
				'permission' => "board.image.delete.other",
				'base_value' => 0,
			],
			[
				'permission' => "board.image.spoiler.upload",
				'base_value' => 1,
			],
			[
				'permission' => "board.image.spoiler.other",
				'base_value' => 0,
			],
		]);
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
