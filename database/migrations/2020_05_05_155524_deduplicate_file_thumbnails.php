<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DeduplicateFileThumbnails extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // not going to bother putting this into eloquent.
        // if you're having problems with this migration, run this.
        // DELETE FROM file_thumbnails a USING (SELECT MIN(file_thumbnail_id) as file_thumbnail_id, source_id, thumbnail_id FROM file_thumbnails GROUP BY source_id, thumbnail_id HAVING COUNT(*) > 1) b WHERE a.source_id = b.source_id AND a.thumbnail_id = b.thumbnail_id AND a.file_thumbnail_id <> b.file_thumbnail_id;

        Schema::table('posts', function (Blueprint $table) {
            $table->timestamp('suppressed_at')->nullable()->default(null)->after('bumplocked_at');
            $table->timestamp('cyclical_at')->nullable()->default(null)->after('stickied_at');
        });

        Schema::table('file_thumbnails', function (Blueprint $table) {
            $table->unique(['source_id', 'thumbnail_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('suppressed_at');
            $table->dropColumn('cyclical_at');
        });

        Schema::table('file_thumbnails', function (Blueprint $table) {
            $table->dropUnique(['source_id', 'thumbnail_id']);
        });
    }
}
