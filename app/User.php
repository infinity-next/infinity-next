<?php namespace App;

use App\Board;
use App\Post;
use App\Contracts\PermissionUser as PermissionUserContract;
use App\Traits\PermissionUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Contracts\Billable as BillableContract;

class User extends Model implements AuthenticatableContract, BillableContract, CanResetPasswordContract, PermissionUserContract {
	
	use Authenticatable, Billable, CanResetPassword, PermissionUser;
	
	/**
	 * Distinguishes this model from an Anonymous user.
	 *
	 * @var boolean
	 */
	protected $anonymous = false;
	
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'user_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['username', 'email', 'password'];
	
	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = ['password', 'remember_token'];
	
	public function payments()
	{
		return $this->hasMany('\App\Payment', 'customer_id', 'user_id');
	}
	
	public function roles()
	{
		return $this->hasMany('\App\UserRole', 'user_id');
	}
}
