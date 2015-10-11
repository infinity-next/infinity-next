<?php

use App\Report;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ReportAddGlobal extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('reports', function(Blueprint $table)
		{
			$table->boolean('global')->default(false)->after('board_uri');
		});
		
		Report::with('post')->chunk(100, function($reports)
		{
			foreach ($reports as $report)
			{
				$report->global    = is_null($report->board_uri);
				$report->board_uri = $report->post->board_uri;
				$report->save();
			}
		});
		
		Schema::table('reports', function(Blueprint $table)
		{
			$table->string('board_uri', 32)->change();
		});
		
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{		
		Schema::table('reports', function(Blueprint $table)
		{
			$table->string('board_uri', 32)->nullable()->change();
		});
		
		Report::with('post')->chunk(100, function($reports)
		{
			foreach ($reports as $report)
			{
				$report->board_uri = $report->global ? null : $report->post->board_uri;
				$report->save();
			}
		});
		
		Schema::table('reports', function(Blueprint $table)
		{
			$table->dropColumn('global');
		});
	}
}
