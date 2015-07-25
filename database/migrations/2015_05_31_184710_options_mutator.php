<?php

use App\Option;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class OptionsMutator extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// Create site settings table
		Schema::create('site_settings', function($table)
		{
			$table->increments('site_setting_id');
			$table->string('option_name');
			$table->binary('option_value');
			
			$table->index('option_name');
			
			// Foreigns and Indexes
			$table->foreign('option_name')
				->references('option_name')->on('options')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		// Create board settings table
		Schema::create('board_settings', function($table)
		{
			$table->increments('board_setting_id');
			$table->string('option_name');
			$table->string('board_uri', 32);
			$table->binary('option_value');
			
			$table->index(['option_name', 'board_uri']);
			
			// Foreigns and Indexes
			$table->foreign('option_name')
				->references('option_name')->on('options')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('board_uri')
				->references('board_uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
		});
		
		// Migrate option values to the new table.
		$siteSettings = [];
		foreach (DB::table('options')->get() as $option)
		{
			$siteSettings[] = [
				'option_name'  => $option->option_name,
				'option_value' => $option->option_value,
			];
		}
		DB::table('site_settings')->insert($siteSettings);
		
		// Mutate the old options table.
		Schema::table('options', function($table)
		{
			$table->enum('option_type', [
				'board',
				'site',
			])->default('site')->after('default_value');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Mutate the old options table.
		Schema::table('options', function($table)
		{
			$table->binary('option_value')->nullable()->after('option_name');
			$table->dropColumn('option_type');
		});
		
		// Migrate option values to the old table.
		$siteSettings = [];
		foreach (DB::table('site_settings')->get() as $option)
		{
			DB::table('site_settings')
				->where('option_name', $option->option_name)
				->update(['option_value' => $option->option_value]);
		}
		
		// Drop new tables
		Schema::drop('board_settings');
		Schema::drop('site_settings');
	}
	
}
