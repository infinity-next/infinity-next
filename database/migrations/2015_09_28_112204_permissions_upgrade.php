<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

use App\Role;

class PermissionsUpgrade extends Migration
{
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::table('user_roles')->where('role_id', '=', Role::ID_REGISTERED)->delete();
		DB::table('roles')->where('role_id', '=', Role::ID_REGISTERED)->delete();
		
		Schema::table('roles', function(Blueprint $table)
		{
			$table->integer('weight')->unsigned();
		});
		
		Schema::table('permissions', function(Blueprint $table)
		{
			$table->dropColumn('base_value');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('roles', function(Blueprint $table)
		{
			$table->dropColumn('weight');
		});
		
		Schema::table('permissions', function(Blueprint $table)
		{
			$table->boolean('base_value')->default(0);
		});
	}
	
}
