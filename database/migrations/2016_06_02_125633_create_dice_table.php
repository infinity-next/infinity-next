<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dice', function(Blueprint $table)
        {
            $table->bigIncrements('dice_id');
            $table->timestamps();
            $table->integer('rolling')->unsigned()->default(1);
            $table->integer('sides')->unsigned()->default(6);

            $table->integer('modifier')->nullable()->default(null);
            $table->integer('greater_than')->nullable()->unsigned()->default(null);
            $table->integer('less_than')->nullable()->unsigned()->default(null);
            $table->integer('minimum')->nullable()->unsigned()->default(null);
            $table->integer('maximum')->nullable()->unsigned()->default(null);

            $table->binary('rolls');
            $table->integer('total')->unsigned();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dice');
    }
}
