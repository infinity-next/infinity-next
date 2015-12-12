<?php namespace App\Providers;

use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Hashing\Hasher as HasherContract;
use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Auth\EloquentUserProvider as LaravelEloquentUserProvider;

class EloquentUserProvider extends LaravelEloquentUserProvider {
	
	/**
	 * Create a new database user provider.
	 *
	 * @param  \Illuminate\Contracts\Hashing\Hasher  $hasher
	 * @param  string  $model
	 * @return void
	 */
	public function __construct(HasherContract $hasher, $model)
	{
		$this->model = $model;
		$this->hasher = $hasher;
	}
	
	/**
	 * Validate a user against the given credentials.
	 *
	 * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
	 * @param  array  $credentials
	 * @return bool
	 */
	public function validateCredentials(UserContract $user, array $credentials)
	{
		$plain = $credentials['password'];
		
		$legacyHasher = $user->getAuthObject();
		
		if ($legacyHasher !== false)
		{
			if (!$legacyHasher->check($plain, $user->getAuthPassword()))
			{
				return false;
			}
			
			$user->password = $this->hasher->make($plain);
			$user->password_legacy = null;
			$user->save();
			
			return true;
		}
		
		return $this->hasher->check($plain, $user->getAuthPassword());
	}
	
}
