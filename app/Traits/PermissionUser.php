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
	 * Can this user administrate ANY board?
	 *
	 * @return boolean
	 */
	public function canAdminBoards()
	{
		return $this->can('sys.boards');
	}
	
	/**
	 * Can this user clear the system cache?
	 *
	 * @return boolean
	 */
	public function canAdminCache()
	{
		return $this->can('sys.cache');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminConfig()
	{
		return $this->can('sys.config');
	}
	
	/**
	 * Can this user administrate the system logs?
	 *
	 * @return boolean
	 */
	public function canAdminLogs()
	{
		return $this->can('sys.logs');
	}
	
	/**
	 * Can this user administrate groups and their permissions?
	 *
	 * @return boolean
	 */
	public function canAdminRoles()
	{
		return $this->can('sys.roles');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminPayments()
	{
		return $this->can('sys.payments');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminPermissions()
	{
		return $this->can('sys.permissions');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminTools()
	{
		return $this->can('sys.tools');
	}
	
	/**
	 * Can this user administrate the system config?
	 *
	 * @return boolean
	 */
	public function canAdminUsers()
	{
		return $this->can('sys.users');
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
	 * Can this user bumplock threads?
	 *
	 * @return boolean
	 */
	public function canBumplock(Post $post)
	{
		return $this->can("board.post.bumplock", $post->board_uri);
	}
	
	/**
	 * Can this user create and assume control of a new board?
	 *
	 * @return boolean
	 */
	public function canCreateBoard()
	{
		return $this->can("board.create");
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
		return $this->can("board.post.delete.other", $board);
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
		if ($this->can("board.post.edit.other", $post))
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
	public function canEditConfig($board = null)
	{
		if (!is_null($board))
		{
			return $this->can("board.config", $board);
		}
		else
		{
			return $this->canAny("board.config");
		}
	}
	
	/**
	 * Can this user edit a board's URI?
	 *
	 * @return boolean
	 */
	public function canEditBoardUri(Board $board)
	{
		return false;
	}
	
	/**
	 * Can this user lock this thread to replies?
	 *
	 * @return boolean
	 */
	public function canLock(Post $post)
	{
		return $this->can("board.post.lock", $post->board_uri);
	}
	
	/**
	 * Can this user post in locked threads?
	 *
	 * @return boolean
	 */
	public function canPostInLockedThreads(Board $board)
	{
		return $this->can('board.post.lock_bypass', $board->board_uri);
	}
	
	/**
	 * Can this user post in this thread without filling out the captcha?
	 *
	 * @return boolean
	 */
	public function canPostWithoutCaptcha(Board $board)
	{
		return $this->can('board.post.nocaptcha', $board->board_uri);
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
	 * Can this user see another's raw IP?
	 *
	 * @return boolean
	 */
	public function canViewRawIP()
	{
		return $this->can('site.user.raw_ip');
	}
	
	
	
	/**
	 * Gets the user's roles with capcodes for this board.
	 * A capcode is a text colum associated with a role.
	 *
	 * @param  \App\Board  $board
	 * @return array|Collection
	 */
	public function getCapcodes(Board $board)
	{
		if (!$this->isAnonymous())
		{
			// Only return roles 
			return $this->roles->filter(function($role) use ($board) {
				
				if (!$role->capcode)
				{
					return false;
				}
				
				if (is_null($role->board_uri) || $role->board_uri == $board->board_uri)
				{
					return true;
				}
			});
		}
		
		return [];
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
		
		return Board::whereIn('board_uri', $boards)
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
			$rememberTags    = ["user.{$this->user_id}", "permissions"];
			$rememberTimer   = 3600;
			$rememberKey     = "user.{$this->user_id}.permissions";
			$rememberClosure = function()
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
			};
			
			switch (env('CACHE_DRIVER'))
			{
				case "file" :
				case "database" :
					$this->permissions = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
					break;
				
				default :
					$this->permissions = Cache::tags($rememberTags)->remember($rememberKey, $rememberTimer, $rememberClosure);
					break;
			}
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
