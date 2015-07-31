<?php namespace App;

use App\Contracts\PermissionUser;
use Illuminate\Database\Eloquent\Model;

class Log extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'logs';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'action_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['action_name', 'action_details', 'user_id', 'user_ip', 'board_uri'];
	
	
	public function board()
	{
		return $this->belongsTo('\App\Board', 'board_uri');
	}
	
	public function user()
	{
		return $this->belongsTo('\App\User', 'user_id');
	}
	
	
	public function getDetails($user = null)
	{
		$details = json_decode($this->action_details, true);
		
		foreach ($details as $detailKey => &$detailValue)
		{
			$methodName = camel_case("get_log_visible_{$detailKey}");
			
			if (method_exists($this, $methodName))
			{
				$detailValue = $this->{$methodName}($detailValue, $user);
			}
		}
		
		return $details;
	}
	
	public function getLogVisibleIp($ip, $user = null)
	{
		if ($user instanceof PermissionUser && $user->canViewRawIP())
		{
			return $ip;
		}
		
		return ip_less($ip);
	}
}