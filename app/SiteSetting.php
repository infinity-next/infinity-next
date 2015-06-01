<?php namespace App;

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
	protected $fillable = ['option_name', 'option_value'];
	
	public $timestamps = false;
	
	
	public function option()
	{
		return $this->belongsTo('\App\Option', 'option_name');
	}
	
}
