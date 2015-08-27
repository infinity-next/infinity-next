<?php

use App\Ban;
use App\Post;
use App\Report;

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
			$table->binary('ban_ip_end')->after('ban_ip');
			$table->binary('ban_ip_start')->after('ban_ip');
		});
		Schema::table('posts', function(Blueprint $table)
		{
			$table->binary('author_ip_bin')->nullable()->after('author_ip');
		});
		Schema::table('reports', function(Blueprint $table)
		{
			$table->binary('reporter_ip')->after('ip');
		});
		
		
		Ban::chunk(100, function($bans)
		{
			foreach ($bans as $ban)
			{
				$ban->ban_ip_start = inet_pton($ban->ban_ip);
				$ban->ban_ip_end   = inet_pton($ban->ban_ip);
				$ban->save();
			}
		});
		
		Post::withTrashed()->chunk(100, function($posts)
		{
			foreach ($posts as $post)
			{
				$post->author_ip_bin = null;
				
				if (!is_null($post->author_ip))
				{
					$post->author_ip_bin = inet_pton($post->author_ip);
				}
				
				$post->save();
			}
		});
		
		Report::chunk(100, function($reports)
		{
			foreach ($reports as $report)
			{
				$report->reporter_ip = inet_pton($report->ip);
				$report->save();
			}
		});
		
		
		Schema::table('bans', function(Blueprint $table)
		{
			$table->dropColumn('ban_ip');
		});
		Schema::table('posts', function(Blueprint $table)
		{
			$table->dropColumn('author_ip');
		});
		Schema::table('posts', function(Blueprint $table)
		{
			$table->renameColumn('author_ip_bin', 'author_ip');
		});
		Schema::table('reports', function(Blueprint $table)
		{
			$table->dropColumn('ip');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('bans', function(Blueprint $table)
		{
			$table->string('ban_ip', 46)->after('ban_ip_end');
		});
		Schema::table('posts', function(Blueprint $table)
		{
			$table->string('author_ip_string', 46)->after('author_ip');
		});
		Schema::table('reports', function(Blueprint $table)
		{
			$table->string('ip', 46)->after('reporter_ip');
		});
		
		
		Ban::chunk(100, function($bans)
		{
			foreach ($bans as $ban)
			{
				$ban->ban_ip_start = inet_ntop($ban->ban_ip);
				$ban->ban_ip_end   = inet_ntop($ban->ban_ip);
				$ban->save();
			}
		});
		
		Post::withTrashed()->chunk(100, function($posts)
		{
			foreach ($posts as $post)
			{
				$post->author_ip_string = null;
				
				if (!is_null($post->author_ip))
				{
					$post->author_ip_string = inet_ntop($post->author_ip);
				}
				
				$post->save();
			}
		});
		
		Report::chunk(100, function($reports)
		{
			foreach ($reports as $report)
			{
				$report->ip = inet_ntop($report->reporter_ip);
				$report->save();
			}
		});
		
		
		Schema::table('bans', function(Blueprint $table)
		{
			$table->dropColumn('ban_ip_start', 'ban_ip_end');
		});
		Schema::table('posts', function(Blueprint $table)
		{
			$table->dropColumn('author_ip');
		});
		Schema::table('posts', function(Blueprint $table)
		{
			$table->renameColumn('author_ip_string', 'author_ip');
		});
		Schema::table('reports', function(Blueprint $table)
		{
			$table->dropColumn('reporter_ip');
		});
	}
}
