<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class BoardSetting extends Model {
	
	use \App\Traits\EloquentBinary;
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'board_settings';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['board_setting_id', 'option_name', 'board_uri', 'option_value'];
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'board_setting_id';
	
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
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board_uri');
	}
	
	/**
	 * Gets our option value and unwraps it from any stream wrappers.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function getOptionValueAttribute($value)
	{
		return binary_unsql($value);
	}
	
	
	/**
	 * Sets our option value and encodes it if required.
	 *
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function setOptionValueAttribute($value)
	{
		$this->attributes['option_value'] = binary_sql($value);
	}
	
}
