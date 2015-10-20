<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBoardAssets extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('board_assets', function(Blueprint $table)
		{
			$table->increments('board_asset_id');
			$table->string('board_uri', 255);
			$table->bigInteger('file_id')->unsigned();
			$table->enum('asset_type', [
				'board_banner',
				'file_deleted',
				'file_none',
				'file_spoiler',
			]);
			$table->timestamps();
			
			// Foreigns and Indexes
			$table->foreign('file_id')
				->references('file_id')->on('files')
				->onUpdate('cascade');
			
			$table->foreign('board_uri')
				->references('board_uri')->on('boards')
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
		Schema::drop('board_assets');
	}

}
