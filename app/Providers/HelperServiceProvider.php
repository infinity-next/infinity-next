<?php namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class HelperServiceProvider extends ServiceProvider {
	
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		//
	}
	
	/**
	 * Register the helper functions.
	 *
	 * Load all helper functions in out app/Helpers/ directory.
	 *
	 * @return void
	 */
	public function register()
	{
		foreach (glob(app_path().'/Helpers/*.php') as $filename){
			require_once($filename);
		}
	}
	
}
