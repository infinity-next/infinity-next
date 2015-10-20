<?php namespace App;

use App\Board;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BoardTag extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'board_tags';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['tag'];
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'board_tag_id';
	
	public $timestamps = false;
	
	
	public function boards()
	{
		return $this->belongsToMany('\App\Board', 'board_tag_assignments', 'board_tag_id', 'board_uri');
	}
	
	/**
	 * Returns the total number of a tag's related board's 'stats_active_usrs'.
	 *
	 * @param  int  $days  Optional. Defaults to 3. Days of records to derive value from.
	 * @return int
	 */
	public function getWeight()
	{
		$weight = 0;
		
		foreach ($this->boards as $board)
		{
			$weight += $board->stats_active_users;
		}
		
		return $weight;
	}
}
