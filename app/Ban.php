<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Ban extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'bans';
	
	/**
	 * The primary key that is used by ::get()
	 *
	 * @var string
	 */
	protected $primaryKey = 'ban_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['ban_ip', 'seen', 'created_at', 'updated_at', 'expires_at', 'mod_id', 'post_id', 'ban_reason_id', 'justification'];
	
	
	public function mod()
	{
		return $this->belongsTo('\App\User', 'user_id', 'mod_id');
	}
	
	public function post()
	{
		return $this->belongsTo('\App\Post', 'post_id');
	}
	
	public static function isBanned($ip, $board = null)
	{
		$board_uri = null;
		
		if ($board instanceof Board)
		{
			$board_uri = $board->board_uri;
		}
		else if ($board != "")
		{
			$board_uri = $board;
		}
		
		return static::getBan($ip, $board_uri) ? true : false;
	}
	
	public static function getBan($ip, $board_uri = null)
	{
		return Ban::ip($ip)
			->board($board_uri)
			->current()
			->get()
			->last();
	}
	
	public function scopeBoard($query, $board_uri = null)
	{
		if (is_null($board_uri))
		{
			return $query->whereNull('board_uri');
		}
		
		return $query
			->where(function($query) use ($board_uri) {
				$query
					->where('board_uri', '=', $board_uri)
					->orWhereNull('board_uri');
			});
	}
	
	public function scopeCurrent($query)
	{
		return $query
			->where(function($query) {
				$query
					->where('expires_at', '>', $this->freshTimestamp())
					->orWhere('seen', 0);
			});
	}
	
	public function scopeIpString($query, $ip)
	{
		return $query->ipBinary(inet_pton($ip));
	}
	
	public function scopeIpBinary($query, $ip)
	{
		return $query->where(function($query) use ($ip) {
				$query->where('ban_ip_start', '<=', $ip);
				$query->where('ban_ip_end',   '>=', $ip);
			});
	}
}