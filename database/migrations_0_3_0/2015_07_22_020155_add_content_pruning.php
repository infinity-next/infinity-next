<?php

use App\Post;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use Illuminate\Support\Facades\DB as DB;

class AddContentPruning extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('posts', function(Blueprint $table)
		{
			$table->timestamp('bumplocked_at')->nullable()->default(null)->after('stickied_at');
			$table->timestamp('locked_at')->nullable()->default(null)->after('bumplocked_at');
			$table->timestamp('bumped_last')->nullable()->default(null)->after('reply_last');
		});
		
		Post::withTrashed()
			->whereNull('bumped_last')
			->update([
				"bumped_last" => DB::raw("reply_last"),
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
			$table->dropColumn('bumplocked_at');
			$table->dropColumn('locked_at');
			$table->dropColumn('bumped_last');
		});
	}
	
}
