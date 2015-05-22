<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OptionGroup extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'option_groups';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['option_group_id', 'group_display_order', 'group_name', 'debug_only', 'display_order'];
	
	/**
	 * Determines if Laravel should set created_at and updated_at timestamps.
	 *
	 * @var array
	 */
	public $timestamps = false;
	
	
	public function options()
	{
		return $this->belongsToMany("\App\Option", 'option_group_assignments', 'option_group_id', 'option_name')->withPivot('display_order');
	}
	
}