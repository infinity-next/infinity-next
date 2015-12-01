<?php

use App\FileStorage;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Intervention\Image\ImageManager;

class AddFileDimensions extends Migration
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
			$table->integer('file_width')->nullable()->after('filesize');
			$table->integer('file_height')->nullable()->after('file_width');
			$table->integer('thumbnail_width')->nullable()->after('has_thumbnail');
			$table->integer('thumbnail_height')->nullable()->after('thumbnail_width');
		});
		
		FileStorage::where('has_thumbnail', true)->where('mime', 'like', 'image/%')->chunk(100, function($files)
		{
			echo "\tMeasuring 100 images.\n";
			
			foreach ($files as $file)
			{
				$image = (new ImageManager)->make($file->getFullPath());
				$file->file_height = $image->height();
				$file->file_width  = $image->width();
				
				$thumb = (new ImageManager)->make($file->getFullPathThumb());
				$file->thumbnail_height = $thumb->height();
				$file->thumbnail_width  = $thumb->width();
				
				$file->save();
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
			$table->dropColumn('file_width');
			$table->dropColumn('file_height');
			$table->dropColumn('thumbnail_width');
			$table->dropColumn('thumbnail_height');
		});
	}
	
}
