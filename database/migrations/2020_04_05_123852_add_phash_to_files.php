<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPhashToFiles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('banned');
            $table->dropColumn('hash');
            $table->dropColumn('has_thumbnail');
            $table->dropColumn('thumbnail_height');
            $table->dropColumn('thumbnail_width');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->date('banned_at')->nullable()->default(null)->after('last_uploaded_at');
            $table->char('hash', 64)->after('file_id');
            $table->bigInteger('phash')->nullable()->default(null)->after('hash');

            $table->bigInteger('source_id')->nullable()->default(null);
            $table->foreign('source_id')
                ->references('file_id')->on('files')
                ->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::rename('file_attachments', 'post_attachments');
        Schema::table('post_attachments', function (Blueprint $table) {
            $table->bigInteger('thumbnail_id')->nullable()->default(null)->after('file_id');

            $table->foreign('thumbnail_id')
                ->references('file_id')->on('files')
                ->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('banned_at');
            $table->dropColumn('hash');
            $table->dropColumn('phash');
            $table->dropColumn('source_id');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->boolean('banned')->default(false);
            $table->char('hash', 32);
            $table->boolean('has_thumbnail');
            $table->integer('thumbnail_width')->nullable()->after('has_thumbnail');
            $table->integer('thumbnail_height')->nullable()->after('thumbnail_width');
        });

        Schema::table('post_attachments', function (Blueprint $table) {
            $table->dropColumn('thumbnail_id');
        });
        Schema::rename('post_attachments', 'file_attachments');
    }
}
