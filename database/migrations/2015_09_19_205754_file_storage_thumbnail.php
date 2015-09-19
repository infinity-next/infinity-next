<?php

use App\FileStorage;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FileStorageThumbnail extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('files', function(Blueprint $table)
		{
			$table->boolean('has_thumbnail')->default(false);
		});
		
		FileStorage::chunk(100, function($files)
		{
			foreach ($files as $file)
			{
				if ($file->hasThumb())
				{
					$file->has_thumbnail = true;
					$file->save();
				}
			}
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('files', function(Blueprint $table)
		{
			$table->dropColumn('has_thumbnail');
		});
	}
	
}
