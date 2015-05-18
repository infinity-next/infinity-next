<?php namespace App\Traits;

use App\Board;
use App\Permission;
use App\Post;
use App\Role;
use App\RolePermission;
use App\UserPermissionCache;
use Request;

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
	 * Return the user's permission cache, if it exists.
	 * build it if nessecary.
	 *
	 * @return array|null
	 */
	protected function getPermissionCache()
	{
		$UserPermissionCache = UserPermissionCache::find($this->user_id);
		
		if ($UserPermissionCache)
		{
			$cache = $UserPermissionCache->first()->cache;
			
			if (!is_null($cache))
			{
				return json_decode($cache, true);
			}
		}
		
		return null;
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
			$this->permissions = $this->getPermissionCache();
			
			if (!$this->permissions)
			{
				$this->permissions = [];
				
				// Fetch our permission mask.
				if ($this->isAnonymous())
				{
					$this->permissions["anonymous"] = Role::getRoleMaskByName("anonymous");
					
					if (!$this->isAccountable())
					{
						$this->permissions["unaccountable"] = Role::getRoleMaskByName("unaccountable");
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
						$this->permissions = Role::getRoleMaskByName("anonymous");
					}
					else
					{
						$this->permissions = Role::getRoleMaskByID($userRoles);
					}
				}
				
				$this->setPermissionCache();
			}
		}
		
		return $this->permissions;
	}
	
	
	/**
	 * Caches a permission mask for this user.
	 *
	 * @return \App\UserPermissionCache
	 */
	protected function setPermissionCache()
	{
		return UserPermissionCache::updateOrCreate([
			'user_id' => $this->user_id,
			'cache'   => json_encode($this->permissions),
		]);
	}
}
