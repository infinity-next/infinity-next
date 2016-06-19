<?php

use App\Post;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPostBodyRtl extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('posts', function(Blueprint $table)
        {
            $table->boolean('body_rtl')->nullable()->default(null)->after('body_html');
        });

        Post::where(DB::raw('1=1'))->update([
            'body_parsed'    => null,
            'body_parsed_at' => null,
            'body_html'      => null,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('posts', function(Blueprint $table)
        {
            $table->dropColumn('body_rtl');
        });
    }
}
