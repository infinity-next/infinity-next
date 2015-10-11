<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSiteConfig extends Migration {
	
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('options', function(Blueprint $table)
		{
			$table->string('option_name');
			$table->binary('default_value')->nullable();
			$table->binary('option_value');
			$table->enum('format', [
				'textbox',
				'spinbox',
				'onoff',
				'onofftextbox',
				'radio',
				'select',
				'checkbox',
				'template',
				'callback',
			]);
			$table->mediumText('format_parameters');
			$table->enum('data_type', [
				'string',
				'integer',
				'numeric',
				'array',
				'boolean',
				'positive_integer',
				'unsigned_integer',
				'unsigned_numeric',
			]);
			$table->string('validation_parameters');
			$table->string('validation_class');
			
			
			$table->primary('option_name');
		});
	}
	
	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('options');
	}

}
