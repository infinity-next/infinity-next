<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PostIpBinary extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('bans', function(Blueprint $table)
		{
			// ban_ip
		});
		
		Schema::table('posts', function(Blueprint $table)
		{
			// author_ip
		});
		
		Schema::table('reports', function(Blueprint $table)
		{
			// ip
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}
}
