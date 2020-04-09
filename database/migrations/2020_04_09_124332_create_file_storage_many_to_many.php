<?php

use App\FileStorage;
use App\FileThumbnail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFileStorageManyToMany extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_thumbnails', function (Blueprint $table) {
            $table->bigIncrements('file_thumbnail_id');

            $table->bigInteger('source_id');
            $table->foreign('source_id')
                ->references('file_id')->on('files')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->bigInteger('thumbnail_id');
            $table->foreign('thumbnail_id')
                ->references('file_id')->on('files')
                ->onDelete('cascade')->onUpdate('cascade');

            $table->index(['source_id', 'thumbnail_id',]);
        });

        foreach (\App\FileStorage::where('source_id') as $file) {
            FileThumbnail::create([
                'source_id' => $file->source_id,
                'thumbnail_id' => $file->file_id,
            ]);
        }

        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('source_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_thumbnails');

        Schema::table('files', function (Blueprint $table) {
            $table->bigInteger('source_id')->nullable()->default(null);
            $table->foreign('source_id')
                ->references('file_id')->on('files')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }
}
