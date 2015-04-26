<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void 
	 */
	public function up()
	{
		Schema::create('posts', function(Blueprint $table)
		{
			// Identifying information
			$table->bigIncrements('id');
			$table->string('uri', 32);
			$table->bigInteger('board_id')->unsigned();
			$table->bigInteger('reply_to')->unsigned()->nullable();
			$table->integer('reply_count')->nullable()->default(0);
			$table->timestamp('reply_last')->nullable();
			
			// Embedded information
			$table->timestamps();
			$table->softDeletes();
			$table->string('author_ip', 16);
			
			
			// Content information
			$table->text('author')->nullable();
			$table->string('subject')->nullable();
			$table->string('email', 254)->nullable();
			$table->text('body')->nullable();
			
			
			// Foreigns and Indexes
			$table->foreign('uri')
				->references('uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('reply_to')
				->references('id')->on('posts')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->unique(array('uri', 'board_id'));
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('posts');
	}

}
