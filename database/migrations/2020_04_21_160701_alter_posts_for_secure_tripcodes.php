<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterPostsForSecureTripcodes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->text('insecure_tripcode')->change();
            $table->renameColumn('insecure_tripcode', 'tripcode')->change();
            $table->text('body_signed')->after('body_html')->nullable();
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
            $table->string('tripcode', 255)->change();
            $table->renameColumn('tripcode', 'insecure_tripcode')->change();
            $table->dropColumn('body_signed');
        });
    }
}
