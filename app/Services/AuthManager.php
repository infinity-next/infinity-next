<?php namespace App\Services;

use App\Providers\EloquentUserProvider;
use Illuminate\Auth\AuthManager as LaravelAuthManager;

class AuthManager extends LaravelAuthManager {
	
	/**
	 * Create an instance of the Eloquent user provider.
	 *
	 * @return \App\Providers\EloquentUserProvider
	 */
	protected function createEloquentProvider()
	{
		$model = $this->app['config']['auth.model'];
		
		return new EloquentUserProvider($this->app['hash'], $model);
	}
	
}