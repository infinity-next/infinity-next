<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class BoardSetting extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'board_setting';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['option_name', 'board_uri', 'option_value'];
	
	public $timestamps = false;
	
	
	public function option()
	{
		return $this->belongsTo('\App\Option', 'option_name');
	}
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board_uri');
	}
	
}
