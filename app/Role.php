<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'roles';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['role', 'board', 'caste', 'inherits', 'name', 'capcode', 'system'];
	
	public $timestamps = false;
	
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board', 'id');
	}
	
	public function inherits()
	{
		return $this->belongsTo('\App\Role', 'inherits', 'id');
	}
	
}
