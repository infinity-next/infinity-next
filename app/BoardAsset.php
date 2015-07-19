<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class BoardAsset extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'board_assets';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['board_uri', 'file_id', 'asset_type'];
	
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board_uri');
	}
	
	public function storage()
	{
		return $this->belongsTo('\App\FileStorage', 'file_id');
	}
}
