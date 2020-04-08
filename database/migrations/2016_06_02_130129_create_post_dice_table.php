<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostDiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('post_dice', function(Blueprint $table)
        {
            $table->bigIncrements('post_dice_id');
            $table->timestamps();
            $table->bigInteger('post_id')->unsigned();
            $table->bigInteger('dice_id')->unsigned();

            $table->text('command_text');
            $table->smallInteger('order')->unsigned()->default(0);

            $table->foreign('dice_id')->references('dice_id')->on('dice')
                ->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('post_id')->references('post_id')->on('posts')
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
        Schema::drop('post_dice');
    }
}
