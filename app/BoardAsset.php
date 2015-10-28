<?php namespace App;

use App\Contracts\PseudoEnum  as PseudoEnumContract;
use App\Traits\PseudoEnum as PseudoEnum;

use Illuminate\Database\Eloquent\Model;

class BoardAsset extends Model implements PseudoEnumContract {
	
	use PseudoEnum;
	
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
	
	/**
	 * Psuedo-enum attributes and their permissable values.
	 *
	 * @var array
	 */
	protected $enum = [
		'asset_type' => [
			'board_banner',
			'board_banned',
			'board_icon',
			'file_deleted',
			'file_spoiler',
		],
	];
	
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
		return "<img src=\"{$this->getURL()}\" alt=\"/{$this->board_uri}/\" class=\"board-asset\" />";
	}
	
	public function getURL()
	{
		return url("{$this->board_uri}/file/{$this->storage->hash}/banner.png");
	}
}
