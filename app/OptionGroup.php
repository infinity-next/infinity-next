<?php namespace App;

use App\Board;

use Illuminate\Database\Eloquent\Model;

class OptionGroup extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'option_groups';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'option_group_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['option_group_id', 'group_display_order', 'group_name', 'debug_only', 'display_order'];
	
	/**
	 * Determines if Laravel should set created_at and updated_at timestamps.
	 *
	 * @var array
	 */
	public $timestamps = false;
	
	
	public function options()
	{
		return $this->belongsToMany("\App\Option", 'option_group_assignments', 'option_group_id', 'option_name')->withPivot('display_order');
	}
	
	
	public static function getSiteConfig()
	{
		return static::with(['options' => function($query)
		{
			$query->where('option_type', "site");
			
			$query->leftJoin('site_settings', function($join) {
				$join->on('site_settings.option_name', '=', 'options.option_name');
			});
			
			$query->addSelect(
				'options.*',
				'site_settings.option_value as option_value'
			);
		}])->get();
	}
	
	public static function getBoardConfig(Board $board)
	{
		return static::with(['options' => function($query) use ($board)
		{
			$query->where('option_type', "board");
			
			$query->leftJoin('board_settings', function($join) use ($board)
			{
				$join->on('board_settings.option_name', '=', 'options.option_name');
				$join->where('board_settings.board_uri', '=', $board->board_uri);
			});
			
			$query->addSelect(
				'options.*',
				'board_settings.option_value as option_value'
			);
		}])->get();
	}
}