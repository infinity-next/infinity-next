<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class OptionGroupAssignment extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'option_group_assignments';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['option_name', 'option_group_id', 'display_order'];
	
	/**
	 * Determines if Laravel should set created_at and updated_at timestamps.
	 *
	 * @var array
	 */
	public $timestamps = false;
	
	
	public function option()
	{
		return $this->belongsTo('\App\Option', 'option_name');
	}
	
	public function group()
	{
		return $this->belongsTo('\App\OptionGroup', 'option_group_id');
	}
}