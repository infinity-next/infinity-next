<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class IndexFileStorageLastUpdatedAt extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->date('fuzzybanned_at')->nullable()->default(null)->after('banned_at');

            $table->index('banned_at');
            $table->index('fuzzybanned_at');
        });

        Schema::table('file_thumbnails', function (Blueprint $table) {
            $table->index('source_id');
            $table->index('thumbnail_id');
        });

        \App\FileStorage::where(\DB::raw('1=1'))->update([
            'fuzzybanned_at' => DB::raw('banned_at'),
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('fuzzybanned_at');

            $table->dropIndex('last_uploaded_at');
            $table->dropIndex('banned_at');
            $table->dropIndex('fuzzybanned_at');
        });

        Schema::table('file_thumbnails', function (Blueprint $table) {
            $table->dropIndex('source_id');
            $table->dropIndex('thumbnail_id');
        });
    }
}
