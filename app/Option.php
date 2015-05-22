<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'options';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'option_name';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['option_name', 'default_value', 'option_value', 'format', 'format_parameters', 'data_type'];
	
	/**
	 * Determines if Laravel should set created_at and updated_at timestamps.
	 *
	 * @var array
	 */
	public $timestamps = false;
	
	
	public function groups()
	{
		return $this->belongsToMany("\App\OptionGroup", 'option_group_assignments', 'option_name', 'option_group_id');
	}
	
}