<?php namespace App;

use App\Board;
use Illuminate\Database\Eloquent\Model;

class PostCite extends Model {
	
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'post_cites';
	
	/**
	 * The database primary key.
	 *
	 * @var string
	 */
	protected $primaryKey = 'post_cite_id';
	
	/**
	 * The attributes that are mass assignable.
	 *
	 * @var array
	 */
	protected $fillable = ['post_id', 'post_board_uri', 'post_board_id', 'cite_id', 'cite_board_uri', 'cite_board_id'];
	
	/**
	 * Indicates their is no autoupdated timetsamps.
	 *
	 * @var boolean
	 */
	public $timestamps = false;
	
	// Post that this citation is made in.
	public function post()
	{
		return $this->belongsTo('\App\Post', 'post_id');
	}
	
	public function postBoard()
	{
		return $this->belongsTo('\App\Board', 'board_uri', 'post_board_uri');
	}
	
	// That that this citation references
	public function cite()
	{
		return $this->belongsTo('\App\Post', 'cite_id', 'post_id');
	}
	
	public function citeBoard()
	{
		return $this->belongsTo('\App\Board', 'board_uri', 'cite_board_uri');
	}
	
	public function getBacklinkHTML(Board $board = null)
	{
		$citeBoard = $this->post_board_uri;
		$citePost  = $this->post_board_id;
		$citeURL   = $this->getBacklinkURL();
		$citeText  = $board ? $this->getBacklinkText($board) : $this->getBacklinkText();
		
		$citeClass = [];
		$citeClass[] = "cite";
		$citeClass[] = "cite-backlink";
		$citeClass[] = "cite-post";
		$citeClass = implode(" ", $citeClass);
		
		return"<a href=\"{$citeURL}\" data-cite-board=\"{$citeBoard}\" data-cite-post=\"{$citePost}\" class=\"{$citeClass}\">{$citeText}</a>";
	}
	
	public function getBacklinkText(Board $board = null)
	{
		if ( ($board instanceof Board) && $board->board_uri === $this->post_board_uri )
		{
			return "&gt;&gt;{$this->post_board_id}";
		}
		else
		{
			return "&gt;&gt;&gt;/{$this->post_board_uri}/{$this->post_board_id}";
		}
		
	}
	
	public function getBacklinkURL()
	{
		return url("/{$this->post_board_uri}/post/{$this->post_board_id}");
	}
}
