<?php namespace App;

use App\Board;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Laravel\Cashier\Billable;
use Laravel\Cashier\Contracts\Billable as BillableContract;

class User extends Model implements AuthenticatableContract, BillableContract, CanResetPasswordContract {
	
	use Authenticatable, Billable, CanResetPassword;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';
	
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
		return $this->hasMany('\App\Payment', 'customer', 'id');
	}
	
	
	/**
	 *
	 *
	 */
	public function canAttach(Board $board)
	{
		if ($this->id == 1)
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 *
	 *
	 */
	public function canEdit(Board $board)
	{
		if ($this->id == 1)
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 *
	 *
	 */
	public function canDelete(Board $board)
	{
		if ($this->id == 1)
		{
			return true;
		}
		
		return false;
	}
}
