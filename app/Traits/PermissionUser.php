<?php namespace App\Traits;

use App\Ban;
use App\Board;
use App\Permission;
use App\Post;
use App\Role;
use App\RolePermission;
use Request;
use Cache;

trait PermissionUser {
	
	/**
	 * Accountability is the property which determines if a user is high-risk.
	 * These users inherit different permissions and limitations to prevent
	 * attacks that involve illegal material if set to false.
	 *
	 * @var boolean
	 */
	protected $accountable;
	
	/**
	 * The $permission array is a derived set of permissions.
	 * It is associative. Each key represents a board.
	 * NULL represents global permissions.
	 * If a value is not set, it is assumed false.
	 *
	 * @var array
	 */
	protected $permissions;
	
	
	/**
	 * Getter for the $accountable property.
	 *
	 * @return boolean
	 */
	public function isAccountable()
	{
		if (!is_bool($this->accountable))
		{
			$this->accountable = true;
		}
		
		return $this->accountable;
	}
	
	/**
	 * Getter for the $anonymous property.
	 * Distinguishes this model from an Anonymous user.
	 * Applied on the model, not the trait.
	 *
	 * @return boolean
	 */
	public function isAnonymous()
	{
		return $this->anonymous;
	}
	
	
	/**
	 * Uses flexible argument options to challenge a permission/board
	 * combination against the user's permission mask.
	 *
	 * @return boolean
	 */
	public function can($permission, $board = null)
	{
		if ($permission instanceof Permission)
		{
			$permission = $permission->permission_id;
		}
		
		if ($board instanceof Board)
		{
			$board = $board->board_uri;
		}
		else if (!is_string($board) && !is_null($board))
		{
			$board = null;
		}
		
		return $this->getPermission($permission, $board);
	}
	
	/**
	 * Accepts a permission and checks if *any* board allows it.
	 *
	 * @return boolean
	 */
	public function canAny($permission)
	{
		if ($permission instanceof Permission)
		{
			$permission = $permission->permission_id;
		}
		
		foreach ($this->getPermissions() as $board_uri => $board_permissions)
		{
			if ($this->getPermission($permission, $board_uri))
			{
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Can this user reply with attachments for this board?
	 *
	 * @return boolean
	 */
	public function canAttach(Board $board)
	{
		// The only thing we care about for this setting is the permission mask.
		return $this->can("board.image.upload", $board);
	}
	
	/**
	 * Can this user ban others from this board?
	 *
	 * @return boolean
	 */
	public function canBan(Board $board)
	{
		// The only thing we care about for this setting is the permission mask.
		return $this->can("board.user.ban.free", $board) || $this->can("board.user.ban.reason", $board);
	}
	
	/**
	 * Can this user ban others across the entire site?
	 *
	 * @return boolean
	 */
	public function canBanGlobally()
	{
		// The only thing we care about for this setting is the permission mask.
		return $this->can("board.user.ban.free") || $this->can("board.user.ban.reason");
	}
	
	/**
	 * Can this user create and assume control of a new board?
	 *
	 * @return boolean
	 */
	public function canCreateBoard()
	{
		return true;
	}
	
	/**
	 * Can this user delete this post?
	 *
	 * @return boolean
	 */
	public function canDelete(Post $post)
	{
		// If we can delete any post for this board ...
		if ($this->can("board.post.delete.other", $post->board_uri))
		{
			// Allow post deletion.
			return true;
		}
		// If the author and our current user share an IP ...
		else if ($post->author_ip == Request::ip())
		{
			// Allow post deletion, if the masks allows it.
			return $this->can("board.post.delete.self", $post->board_uri);
		}
		
		return false;
	}
	
	/**
	 * Can this user delete on this board?
	 *
	 * @return boolean
	 */
	public function canDeleteLocally(Board $board)
	{
		return $this->can("board.post.delete.other", $board->board_uri);
	}
	
	/**
	 * Can this user delete posts across the entire site?
	 *
	 * @return boolean
	 */
	public function canDeleteGlobally()
	{
		return $this->can("board.post.delete.other");
	}
	
	/**
	 * Can this user edit this post?
	 *
	 * @return boolean
	 */
	public function canEdit(Post $post)
	{
		// If we can edit any post for this board ...
		if ($this->can("board.post.edit.other", $post->board_uri))
		{
			// Allow post edit.
			return true;
		}
		// If the author and our current user share an IP ...
		else if ($post->author_ip == Request::ip())
		{
			// Allow post edit, if the masks allows it.
			return $this->can("board.post.edit.self", $post->board_uri);
		}
		
		return false;
	}
	
	/**
	 * Can this user edit this board's config?
	 *
	 * @return boolean
	 */
	public function canEditConfig($board)
	{
		$board_uri = $board;
		
		if ($board instanceof Board)
		{
			$board_uri = $board->board_uri;
		}
		
		return $this->can("board.config", $board_uri);
	}
	
	/**
	 * Can this user report this post?
	 *
	 * @return boolean
	 */
	public function canPostWithoutCaptcha(Board $board)
	{
		return !$this->isAnonymous();
	}
	
	/**
	 * Can this user report this post?
	 *
	 * @return boolean
	 */
	public function canReport(Post $post)
	{
		// We can always report a thread, for now.
		return true;
	}
	
	/**
	 * Can this user sticky a thread?
	 *
	 * @return boolean
	 */
	public function canSticky(Post $post)
	{
		// We can only ever sticky a thread, for now.
		if (is_null($post->reply_to))
		{
			return $this->can("board.post.sticky", $post->board_uri);
		}
		
		return false;
	}
	
	
	/**
	 * Returns a list of board_uris where the canEditConfig permission is given.
	 *
	 */
	public function getBoardsWithConfigRights()
	{
		$boards = [];
		
		foreach ($this->getPermissions() as $board_uri => $permissions)
		{
			if ($this->canEditConfig($board_uri))
			{
				if ($board_uri == "")
				{
					return Board::andCreator()
						->andOperator()
						->get();
				}
				else
				{
					$boards[] = $board_uri;
				}
			}
		}
		
		return Board::find($boards)
			->andCreator()
			->andOperator()
			->get();
	}
	
	/**
	 * Determine the user's permission for a specific item.
	 *
	 * @return boolean
	 */
	protected function getPermission($permission, $board = null)
	{
		$permissions = $this->getPermissions();
		
		// Determine the branch for this permission mask.
		if ($this->isAnonymous())
		{
			// If the user is anonymous (no account),
			// then the permission mask is under anonymous.
			$permissionMask = &$permissions['anonymous'];
			
			// If the user is unaccountable (Tor),
			// then the permission mask is instead under unaccountable.
			if (!$this->isAccountable())
			{
				$permissionMask = &$permissions['unaccountable'];
			}
		}
		else
		{
			// Users with accounts are always accountable.
			// Their permission masks do not have branches.
			$permissionMask = &$permissions;
		}
		
		// Check for a localized permisison.
		if (isset($permissionMask[$board][$permission]))
		{
			return $permissionMask[$board][$permission];
		}
		// Check for a global permission.
		else if (isset($permissionMask[null][$permission]))
		{
			return $permissionMask[null][$permission];
		}
		
		// Assume false if not explicitly set.
		return false;
	}
	
	/**
	 * Return the user's entire permission object,
	 * build it if nessecary.
	 *
	 * @return array
	 */
	protected function getPermissions()
	{
		if (!isset($this->permissions))
		{
			$this->permissions = Cache::remember("user.{$this->user_id}.permissions", 3600, function()
			{
				$permissions = [];
				
				// Fetch our permission mask.
				if ($this->isAnonymous())
				{
					$permissions["anonymous"] = Role::getRoleMaskByName("anonymous");
					
					if (!$this->isAccountable())
					{
						$permissions["unaccountable"] = Role::getRoleMaskByName("unaccountable");
					}
				}
				else
				{
					$userRoles = [];
					
					foreach ($this->roles as $role)
					{
						$userRoles[] = $role->role_id;
					}
					
					if (!count($userRoles))
					{
						$permissions = Role::getRoleMaskByName("anonymous");
					}
					else
					{
						$permissions = Role::getRoleMaskByID($userRoles);
					}
				}
				
				return $permissions;
			});
		}
		
		return $this->permissions;
	}
	
	/**
	 * Drops the user's permission cache.
	 *
	 * @return void.
	 */
	public function forgetPermissions()
	{
		Cache::forget("user.{$this->user_id}.permissions");
	}
}
