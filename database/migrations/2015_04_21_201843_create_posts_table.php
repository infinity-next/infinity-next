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
			$table->bigIncrements('post_id');
			$table->string('board_uri', 32);
			$table->bigInteger('board_id')->unsigned();
			$table->bigInteger('reply_to')->unsigned()->nullable();
			$table->integer('reply_count')->nullable()->default(0);
			$table->timestamp('reply_last')->nullable();
			
			// Embedded information
			$table->timestamps();
			$table->integer('updated_by')->unsigned()->nullable();
			$table->softDeletes();
			$table->string('author_ip', 46);
			
			// Content information
			$table->text('author')->nullable();
			$table->integer('capcode_id')->unsigned()->nullable()->default(null);
			$table->string('subject')->nullable();
			$table->string('email', 254)->nullable();
			$table->text('body')->nullable();
			$table->string('password')->nullable()->default(null);
			
			
			// Foreigns and Indexes
			$table->foreign('board_uri')
				->references('board_uri')->on('boards')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->foreign('reply_to')
				->references('post_id')->on('posts')
				->onDelete('cascade')->onUpdate('cascade');
			
			$table->unique(array('board_uri', 'board_id'));
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
