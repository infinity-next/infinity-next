<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'permission_groups';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'permission_group_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['permission_group_id', 'group_display_order', 'group_name', 'debug_only', 'display_order'];
	
	/**
	 * Determines if Laravel should set created_at and updated_at timestamps.
	 *
	 * @var array
	 */
	public $timestamps = false;
	
	
	public function assignments()
	{
		return $this->hasMany('\App\PermissionGroupAssignment', 'permission_group_id');
	}
	
	public function permissions()
	{
		return $this->belongsToMany("\App\Permission", 'permission_group_assignments', 'permission_group_id', 'permission_id')->withPivot('display_order');
	}
	
	
	public function scopeWithPermissions($query)
	{
		return $query->with('permissions');
	}
}