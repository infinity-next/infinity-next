<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveEnumColumns extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// SQLite cannot use the Enum data type, so we're going to change these to strings.
		
		Schema::table('options', function(BLueprint $table)
		{
			$table->dropColumn('format');
			$table->dropColumn('data_type');
			$table->dropColumn('option_type');
		});
		
		// I have to run a second transaction for adding columns because otherwise it doesn't recognize they're gone.
		Schema::table('options', function(BLueprint $table)
		{
			$table->string('option_type', 24)->after('default_value');
			$table->string('format', 24)->after('option_type');
			$table->string('data_type', 24)->after('format_parameters');
		});
		
		
		Schema::table('board_assets', function(Blueprint $table)
		{
			$table->dropColumn('asset_type');
		});
		
		Schema::table('board_assets', function(Blueprint $table)
		{
			$table->string('asset_type', 24)->after('file_id');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('options', function(BLueprint $table)
		{
			$table->dropColumn('format');
			$table->dropColumn('data_type');
			$table->dropColumn('option_type');
		});
		
		Schema::table('options', function(BLueprint $table)
		{
			$table->enum('option_type', [
				'board',
				'site',
			])->default('site')->after('default_value');
			
			$table->enum('format', [
				'textbox',
				'spinbox',
				'onoff',
				'onofftextbox',
				'radio',
				'select',
				'checkbox',
				'template',
				'callback',
			])->after('option_type');
			
			$table->enum('data_type', [
				'string',
				'integer',
				'numeric',
				'array',
				'boolean',
				'positive_integer',
				'unsigned_integer',
				'unsigned_numeric',
			])->after('format_parameters');
			
		});
		
		
		Schema::table('board_assets', function(Blueprint $table)
		{
			$table->dropColumn('asset_type');
		});
		
		Schema::table('board_assets', function(Blueprint $table)
		{
			$table->enum('asset_type', [
				'board_banner',
				'file_deleted',
				'file_none',
				'file_spoiler',
			])->after('file_id');
		});
	}
	
}
