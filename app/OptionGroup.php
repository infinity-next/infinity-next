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
	
	/**
	 * A pseudo-attribute which is assigned during compilation of board and site configs.
	 *
	 * @var mixed
	 */
	public $option_value;
	
	public function assignments()
	{
		return $this->hasMany('\App\OptionGroupAssignment', 'option_group_id');
	}
	
	public function options()
	{
		return $this->belongsToMany("\App\Option", 'option_group_assignments', 'option_group_id', 'option_name')->withPivot('display_order');
	}
	
	
	/**
	 * Return the display name of this option group for UIs.
	 *
	 * @return mixed
	 */
	public function getDisplayName()
	{
		return trans("config.legend.{$this->group_name}");
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
			
			$query->orderBy('display_order', 'asc');
		}])->orderBy('display_order', 'asc')->get();
	}
	
	public static function getBoardConfig(Board $board)
	{
		return static::with(['options' => function($query) use ($board)
		{
			$query->andBoardSettings($board);
		}])->orderBy('display_order', 'asc')->get();
	}
	
}
