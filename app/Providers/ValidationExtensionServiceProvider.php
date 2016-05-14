<?php namespace App\Providers;

use App\Validators\EncodingValidator;
use App\Validators\CssValidator;
use App\Validators\FileValidator;
use Illuminate\Support\ServiceProvider;
use Validator;

class ValidationExtensionServiceProvider extends ServiceProvider
{
	
	public function boot()
	{
		$this->registerValidationRules($this->app['validator']);
	}
	
	protected function registerValidationRules($validator)
	{
		$validator->extend('greater_than',   'App\Validators\ComparisonValidator@validateGreaterThan');
		$validator->extend('less_than',      'App\Validators\ComparisonValidator@validateLessThan');
		
		$validator->extend('css',            'App\Validators\CSSValidator@validateCSS');
		
		$validator->extend('encoding',       'App\Validators\EncodingValidator@validateEncoding');
		
		$validator->extend('md5',            'App\Validators\FileValidator@validateMd5');
		$validator->extend('file_name',      'App\Validators\FileValidator@validateFileName');
		$validator->extend('file_integrity', 'App\Validators\FileValidator@validateFileIntegrity');
		$validator->extend('file_new',       'App\Validators\FileValidator@validateFileNew');
		$validator->extend('file_old',       'App\Validators\FileValidator@validateFileOld');
	}
	
	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}
	
}
