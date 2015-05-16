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
	 *
	 *
	 */
	public function can($permission, $board = null)
	{
		if ($permission instanceof Permission)
		{
			$permission = $permission->permission;
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
		
		// Check for a localized permisison.
		if (isset($permissions[$board][$permission]))
		{
			return $permissions[$board][$permission];
		}
		// Check for a global permission.
		else if (isset($permissions[null][$permission]))
		{
			return $permissions[null][$permission];
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
			return $UserPermissionCache->first()->cache;
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
				$userRoles         = [];
				$roleMasks         = [];
				
				
				if ($this->isAnonymous())
				{
					$roleMasks[] = "anonymous";
				}
				else
				{
					foreach ($this->roles as $role)
					{
						$userRoles[] = $role->role_id;
					}
					
					if (!count($userRoles))
					{
						$roleMasks[] = "anonymous";
					}
				}
				
				if (!$this->isAccountable())
				{
					$roleMasks[] = "unaccountable";
				}
				
				$roles = Role::whereIn('role', $roleMasks)
					->orWhereIn('role_id', $userRoles)
					->with('permissions')
					->get();
				
				foreach ($roles as $role)
				{
					if (!isset($this->permissions[$role->board_uri]))
					{
						$this->permissions[$role->board_uri] = [];
					}
					
					foreach ($role->permissions as $permission)
					{
						if (is_null($permission->board_uri))
						{
							$this->permissions[null][$permission->permission_id] = !!$permission->pivot->value;
						}
					}
				}
			}
		}
		
		return $this->permissions;
	}
}
