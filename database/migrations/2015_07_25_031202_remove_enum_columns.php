<?php

use App\BoardAssets;
use App\Options;

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
		$this->dropEnumColumns();
		
		// I have to run a second transaction for adding columns because otherwise it doesn't recognize they're gone.
		Schema::table('options', function(BLueprint $table)
		{
			$table->string('format', 24)->default('textbox')->after('option_type');
			$table->string('option_type', 24)->default('string')->after('default_value');
			$table->string('data_type', 24)->default('board')->after('format_parameters');
			
			$table->binary('option_value')->nullable()->change();
			$table->binary('format_parameters')->nullable()->change();
			$table->binary('validation_class')->nullable()->change();
			$table->binary('validation_parameters')->nullable()->change();
		});
		
		
		Schema::table('board_assets', function(Blueprint $table)
		{
			if (Schema::hasColumn('board_assets', 'asset_type'))
			{
				$table->dropColumn('asset_type');
			}
		});
		
		Schema::table('board_assets', function(Blueprint $table)
		{
			$table->string('asset_type', 24)->default('board_banner')->after('file_id');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		$this->dropEnumColumns();
		
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
			])->default('textbox')->after('option_type');
			
			$table->enum('data_type', [
				'string',
				'integer',
				'numeric',
				'array',
				'boolean',
				'positive_integer',
				'unsigned_integer',
				'unsigned_numeric',
			])->default('string')->after('format_parameters');
			
		});
		
		
		Schema::table('board_assets', function(Blueprint $table)
		{
			if (Schema::hasColumn('board_assets', 'asset_type'))
			{
				$table->dropColumn('asset_type');
			}
		});
		
		Schema::table('board_assets', function(Blueprint $table)
		{
			$table->enum('asset_type', [
				'board_banner',
				'file_deleted',
				'file_none',
				'file_spoiler',
			])->default('board_banner')->after('file_id');
		});
	}
	
	private function dropEnumColumns()
	{
		if (Schema::hasColumn('options', 'format'))
		{
			Schema::table('options', function(BLueprint $table)
			{
				$table->dropColumn('format');
			});
		}
		
		if (Schema::hasColumn('options', 'data_type'))
		{
			Schema::table('options', function(BLueprint $table)
			{
				$table->dropColumn('data_type');
			});
		}
		
		if (Schema::hasColumn('options', 'option_type'))
		{
			Schema::table('options', function(BLueprint $table)
			{
				$table->dropColumn('option_type');
			});
		}
	}
}
