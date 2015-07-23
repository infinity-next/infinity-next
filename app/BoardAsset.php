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
	 * The database primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'board_asset_id';
	
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
	
	
	public function asHTML()
	{
		return "<img src=\"/{$this->getURL()}\" alt=\"/{$this->board_uri}/\" class=\"board-banner\" />";
	}
	
	public function getURL()
	{
		return "{$this->board_uri}/file/{$this->storage->hash}/banner.png";
	}
}
