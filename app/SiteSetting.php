<?php namespace App;

use App\SiteSetting;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'site_settings';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['site_setting_id', 'option_name', 'option_value'];
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'site_setting_id';
	
	public $timestamps = false;
	
	
	public function option()
	{
		return $this->belongsTo('\App\Option', 'option_name');
	}
	
	
	/**
	 * Return all site settings.
	 *
	 * @return Collection
	 */
	public static function getAll()
	{
		return static::get();
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
	 * Return a specific site setting.
	 *
	 * @return mixed
	 */
	public static function getValue($site_setting)
	{
		foreach (static::getAll() as $setting)
		{
			if ($setting->option_name == $site_setting)
			{
				return $setting->option_value;
			}
		}
		
		return null;
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