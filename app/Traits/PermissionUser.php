<?php namespace App\Traits;

use App\Ban;
use App\Board;
use App\Option;
use App\Permission;
use App\Post;
use App\Role;
use App\RoleCache;
use App\RolePermission;
use App\Contracts\PermissionUser as PermissionUserContract;
use App\Support\IP\CIDR;

use Illuminate\Database\Eloquent\Collection;

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
	 * @param  string  $permission  The permission ID we're checking for.
	 * @param  \App\Board|\App\Post|string|null  Optional. Board, Post, board_uri string, or NULL. If NULL, checks only global permissions.
	 * @return boolean
	 */
	public function can($permission, $board = null)
	{
		if ($permission instanceof Permission)
		{
			$permission = $permission->permission_id;
		}
		
		if ($board instanceof Board || $board instanceof Post)
		{
			$board = $board->board_uri;
		}
		else if (!is_string($board))
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
	 * Returns a list of direct, extant Board URIs where this permission exists.
	 * The goal of this is to weed out loose permissions provided by global permissions.
	 *
	 * @return array (of board_uri)
	 */
	public function canInBoards($permission)
	{
		$boards = [];
		
		if ($permission instanceof Permission)
		{
			$permission = $permission->permission_id;
		}
		
		foreach ($this->getPermissions() as $board_uri => $board_permissions)
		{
			if (strlen($board_uri) === 0)
			{
				continue;
			}
			
			if ($this->getPermission($permission, $board_uri))
			{
				$boards[] = $board_uri;
			}
		}
		
		return $boards;
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
	 * Can this user reply with newly uploaded attachments for this board?
	 *
	 * @return boolean
	 */
	public function canAttachNew(Board $board)
	{
		// The only thing we care about for this setting is the permission mask.
		return $this->can("board.image.upload.new", $board);
	}
	
	/**
	 * Can this user reply with previously uploaded attachments for this board?
	 *
	 * @return boolean
	 */
	public function canAttachOld(Board $board)
	{
		// The only thing we care about for this setting is the permission mask.
		return $this->can("board.image.upload.old", $board);
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
		// We can only ever sticky a thread, for now.
		if (is_null($post->reply_to))
		{
			return $this->can("board.post.bumplock", $post->board_uri);
		}
		
		return false;
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
	 * Can this user create a user?
	 *
	 * @return boolean
	 */
	public function canCreateUser()
	{
		return $this->can("site.user.create");
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
	 * Can this user delete on this board with a password?
	 *
	 * @return boolean
	 */
	public function canDeletePostWithPassword(Board $board)
	{
		return $this->can("board.post.delete.self", $board);
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
		else if (!is_null($post->author_ip) && $post->author_ip->is(Request::ip()))
		{
			// Allow post edit, if the masks allows it.
			return $this->can("board.post.edit.self", $post->board_uri);
		}
		
		return false;
	}
	
	/**
	 * Can this user edit any board's config?
	 *
	 * @return boolean
	 */
	public function canEditAnyConfig()
	{
		return $this->canAny("board.config");
	}
	
	/**
	 * Can this user edit this board's config?
	 *
	 * @return boolean
	 */
	public function canEditConfig($board = null)
	{
		return $this->can("board.config", $board);
	}
	
	/**
	 * Can edit a post with a password?
	 *
	 * @param  \App\Board  $board
	 * @return bool
	 */
	public function canEditPostWithPassword(Board $board)
	{
		return $this->can("board.post.edit.self", $board);
	}
	
	/**
	 * Can this user edit a board setting?
	 *
	 * @param  \App\Board  $board  Board which this setting belongs to.
	 * @param  \App\Option  $option  Option, usually with BoardSetting data embedded, that is being checked.
	 * @return boolean
	 */
	public function canEditSetting(Board $board, Option $option)
	{
		if ($this->canEditConfig($board))
		{
			if ($option->isLocked())
			{
				return $this->canEditSettingLock($board, $option);
			}
			
			return true;
		}
		
		return false;
	}
	
	/**
	 * Can this user edit a board setting lock?
	 *
	 * @param  \App\Board  $board  Board which this setting belongs to.
	 * @param  \App\Option  $option  Option, usually with BoardSetting data embedded, that is being checked.
	 * @return boolean
	 */
	public function canEditSettingLock(Board $board, Option $option)
	{
		return $this->can("site.board.setting_lock", $board);
	}
	
	/**
	 * Can this user edit this staff member on this board?
	 *
	 * @return boolean
	 */
	public function canEditBoardStaffMember(PermissionUserContract $user, Board $board)
	{
		if ($this->canAdminConfig())
		{
			return true;
		}
		
		if ($user->user_id == $this->user_id)
		{
			return false;
		}
		
		return $this->can("board.config", $board);
	}
	
	/**
	 * Can this user edit a board's URI?s
	 *
	 * @return boolean
	 */
	public function canEditBoardUri(Board $board)
	{
		return false;
	}
	
	/**
	 * Can this user feature a post globally?
	 *
	 * @return boolean
	 */
	public function canFeatureGlobally(Post $post)
	{
		return $this->can('sys.config');
	}
	
	/**
	 * Can this user lock this thread to replies?
	 *
	 * @return boolean
	 */
	public function canLock(Post $post)
	{
		// We can only ever sticky a thread, for now.
		if (is_null($post->reply_to))
		{
			return $this->can("board.post.lock", $post);
		}
		
		return false;
	}
	
	/**
	 * Can this user post in locked threads?
	 *
	 * @return boolean
	 */
	public function canPostInLockedThreads(Board $board = null)
	{
		return $this->can('board.post.lock_bypass', $board);
	}
	
	/**
	 * Can this user post in this thread without filling out the captcha?
	 *
	 * @return boolean
	 */
	public function canPostWithoutCaptcha(Board $board = null)
	{
		return $this->can('sys.nocaptcha', $board);
	}
	
	/**
	 * Can this user post a new reply to an existing thread
	 *
	 * @return boolean
	 */
	public function canPostReply(Board $board = null)
	{
		return $this->can('board.post.create.reply', $board);
	}
	
	/**
	 * Can this user manage appeals for a specific board or globally?
	 *
	 * @return boolean
	 */
	public function canManageAppeals(Board $board = null)
	{
		return $this->can('board.user.unban', $board);
	}
	
	/**
	 * Can this user manage appeals for any board?
	 *
	 * @return boolean
	 */
	public function canManageAppealsAny()
	{
		return $this->canAny('board.user.unban');
	}
	
	/**
	 * Returns a list of boards that this user can manage ban appeals in.
	 *
	 * @return array  of board URis.
	 */
	public function canManageAppealsIn()
	{
		return $this->canInBoards('board.user.unban');
	}
	
	/**
	 * Can this user post a new thread
	 *
	 * @return boolean
	 */
	public function canPostThread(Board $board = null)
	{
		return $this->can('board.post.create.thread', $board);
	}
	
	/**
	 * Can remove attachments from post with password?
	 *
	 * @param  \App\Board  $board
	 * @return bool
	 */
	public function canRemoveAttachmentWithPassword(Board $board)
	{
		return $this->can("board.image.delete.self", $board);
	}
	
	/**
	 * Can this user report this post?
	 *
	 * @return boolean
	 */
	public function canReport(Post $post)
	{
		return $this->can('board.post.report', $post->board_uri);
	}
	
	/**
	 * Can this user report this post?
	 *
	 * @return boolean
	 */
	public function canReportGlobally(Post $post)
	{
		return $this->can('site.post.report');
	}
	
	/**
	 * Can this user delete on this board?
	 *
	 * @return boolean
	 */
	public function canSpoilerAttachmentLocally(Board $board)
	{
		return $this->can("board.image.spoiler.other", $board);
	}
	
	/**
	 * Can spoiler/unspoiler attachments from post with password?
	 *
	 * @param  \App\Board  $board
	 * @return bool
	 */
	public function canSpoilerAttachmentWithPassword(Board $board)
	{
		return $this->can("board.image.spoiler.self", $board);
	}
	
	/**
	 * Can this user delete posts across the entire site?
	 *
	 * @return boolean
	 */
	public function canSpoilerAttachmentGlobally()
	{
		return $this->can("board.image.spoiler.other");
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
	 * Can this user supply a password for any purpose?
	 *
	 * @param  \App\Board  $board
	 * @return boolean
	 */
	public function canUsePassword(Board $board)
	{
		if ($this->canDeletePostWithPassword($board))
		{
			return true;
		}
		
		if ($this->canEditPostWithPassword($board))
		{
			return true;
		}
		
		if ($this->canRemoveAttachmentWithPassword($board))
		{
			return true;
		}
		
		if ($this->canSpoilerAttachmentWithPassword($board))
		{
			return true;
		}
		
		return false;
	}
	
	/**
	 * Can this user view this ban?
	 *
	 * @param  \App\Ban The ban we're checking to see if we can view.
	 * @return boolean
	 */
	public function canViewBan(Ban $ban)
	{
		return $this->can('board.bans', $ban->board_uri);
	}
	
	/**
	 * Can this user view a post's local history?
	 *
	 * @param  \App\Post  $post  The post we want to see if we can check the history of locally.
	 * @return boolean
	 */
	public function canViewHistory(Post $post)
	{
		return $this->can('board.history', $post->board_uri);
	}
	
	/**
	 * Can this user view moderator logs?
	 *
	 * @param  \App\Board
	 * @return boolean
	 */
	public function canViewLogs(Board $board)
	{
		return $this->can('board.logs', $board);
	}
	
	/**
	 * Can this user view an addresses's global history?
	 *
	 * @return boolean
	 */
	public function canViewGlobalHistory()
	{
		return $this->can('board.history', null);
	}
	
	/**
	 * Can this user view a board's reports?
	 *
	 * @return boolean
	 */
	public function canViewReports($board = null)
	{
		if ($board instanceof Board)
		{
			return $this->canAny('board.reports', $board->board_uri);
		}
		
		return $this->canAny('board.reports');
	}
	
	/**
	 * Can this user report this post?
	 *
	 * @return boolean
	 */
	public function canViewReportsGlobally()
	{
		return $this->can('site.reports');
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
	 * Can this user see another's raw IP?
	 *
	 * @return boolean
	 */
	public function canViewUnindexedBoards()
	{
		return $this->can('site.board.view_unindexed');
	}
	
	/**
	 * Can this user view a board setting lock?
	 *
	 * @param  \App\Board  $board  Board which this setting belongs to.
	 * @param  \App\Option  $option  Option, usually with BoardSetting data embedded, that is being checked.
	 * @return boolean
	 */
	public function canViewSettingLock(Board $board, Option $option)
	{
		return $option->isLocked() || $this->canEditSettingLock($board, $option);
	}
	
	/**
	 * Drops the user's permission cache.
	 *
	 * @return void.
	 */
	public function forgetPermissions()
	{
		switch (env('CACHE_DRIVER'))
		{
			case "file" :
				Cache::forget("user.{$this->user_id}.permissions");
				break;
			
			case "database" :
				DB::table('cache')
					->where('key', 'like', "%user.{$this->user_id}.%")
					->delete();
				break;
			
			default :
				Cache::tags("user.{$this->user_id}")->flush();
				break;
		}
	}
	
	/**
	 * Returns a complete list of roles that this user may delegate to others.
	 *
	 * @param  Board|null  $board  If not null, will refine search to a single board.
	 * @return Collection|array
	 */
	public function getAssignableRoles(Board $board = null)
	{
		return $board->roles()
			->where(function($query) use ($board)
			{
				if (is_null($board) && $this->canAdminRoles())
				{
					$query->whereStaff();
				}
				else if ($this->can('board.user.role', $board))
				{
					$query->whereJanitor();
				}
			})
			->get();
	}
	
	/**
	 * Returns a list of board_uris where the canEditConfig permission is given.
	 *
	 * @return Collection  of Board
	 */
	public function getBoardsWithAssetRights()
	{
		return $this->getBoardsWithConfigRights();
	}
	
	/**
	 * Returns a list of board_uris where the canEditConfig permission is given.
	 *
	 * @return Collection  of Board
	 */
	public function getBoardsWithConfigRights()
	{
		$whitelist = true;
		$boardlist = [];
		
		if ($this->canEditConfig(null))
		{
			$whitelist = false;
		}
		
		foreach ($this->getPermissions() as $board_uri => $permission)
		{
			if ($this->canEditConfig($board_uri) === $whitelist)
			{
				$boardlist[] = $board_uri;
			}
		}
		
		$boardlist = array_unique($boardlist);
		
		return Board::where(function($query) use ($whitelist, $boardlist) {
				if ($whitelist)
				{
					$query->whereIn('board_uri', $boardlist);
				}
				else
				{
					$query->whereNotIn('board_uri', $boardlist);
				}
			})
			->andAssets()
			->andCreator()
			->andOperator()
			->andStaffAssignments()
			->paginate(25);
	}
	
	/**
	 * Returns a list of board_uris where the canEditConfig permission is given.
	 *
	 * @return Collection  of Board
	 */
	public function getBoardsWithStaffRights()
	{
		return $this->getBoardsWithConfigRights();
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
	 * Returns the name of the user that should be displayed in public.
	 *
	 * @return string
	 */
	public function getDisplayName()
	{
		return $this->isAnonymous() ? trans('board.anonymous') : $this->username;
	}
	
	/**
	 * Determine the user's permission for a specific item.
	 *
	 * @param  string  $permission  The permission ID we are checking.
	 * @param  string|null  $board_uri  The board URI we're checking against. NULL means global only.
	 * @return boolean
	 */
	protected function getPermission($permission, $board_uri = null)
	{
		$boardPermissions  = $this->getPermissionsForBoard($board_uri);
		$globalPermissions = $this->getPermissionsForBoard();
		
		// Check for a board permisison.
		if (isset($boardPermissions[$permission]))
		{
			return $boardPermissions[$permission];
		}
		// Check for a global permission.
		else if (isset($globalPermissions[$permission]))
		{
			return $globalPermissions[$permission];
		}
		
		// Assume false if not explicitly set.
		return false;
	}
	
	/**
	 * Returns permissions for global+board belonging to our current route.
	 *
	 * @param  string|null  $board_uri
	 * @return array
	 */
	public function getPermissionsForBoard($board_uri = null)
	{
		// Default permission mask is normal.
		$mask = "normal";
		
		// If the user is from Tor, they are instead unaccountable.
		if (!$this->isAccountable())
		{
			$mask = "unaccountable";
		}
		
		return $this->getPermissionsWithRoutes($board_uri, $mask);
	}
	
	/**
	 * Returns permission masks for each route.
	 * This is where permissions are interpreted.
	 *
	 * @param  string|null  $board_uri
	 * @return array
	 */
	protected function getPermissionMask($board_uri = null)
	{
		// Get our routes.
		$routes = $this->getPermissionRoutes();
		
		// Build a route name to empty array relationship.
		$permissions = array_combine(array_keys($routes), array_map(function($n) {
			return [];
		}, $routes));
		
		// There are two kinds of permission assignments.
		// 1. Permissions that belong to the route.
		// 2. Permissions directly assigned to the user.
		// 
		// When a permission is a part of a major mask branch (identified in getPermissionRoutes),
		// then any role with that role name becomes a part of the mask.
		// 
		// When a permission is directly assigned to the user, then only that mask and its
		// inherited mask are incorporated. Inheritance only goes up one step for right now.
		
		$allGroups = [];
		
		// Pull each route and add its groups to the master collection.
		foreach ($routes as $branch => $roleGroups)
		{
			$allGroups = array_merge($allGroups, $roleGroups);
		}
		
		// We only want uniques.
		$allGroups = array_unique($allGroups);
		
		// In order to determine if we want to include a role in a specific mask,
		// we must also pull a user's roles to see what is directly applied to them.
		$userRoles = $this->getRoles()->modelKeys();
		
		$parentRoles = Role::where('system', true)->with('permissions')->get()->getDictionary();
		
		// Write out a monster query to pull precisely what we need to build our permission masks.
		$query = Role::where(function($query) use ($board_uri, $allGroups)
		{
			$query->where(function($query) use ($board_uri) {
				$query->orWhereNull('board_uri');
				$query->orWhere('board_uri', $board_uri);
			});
			
			// Pull any role that belongs to our masks's route.
			$query->whereIn('roles.role', $allGroups);
			
			// If we're not anonymous, we also need directly assigned roles.
			if (!$this->isAnonymous())
			{
				$query->orWhereHas('users', function($query) {
					//$query->where( \DB::raw("`user_roles`.`user_id`"), $this->user_id);
					$query->where("user_roles.user_id", $this->user_id);
				});
			}
			else
			{
				$query->whereDoesntHave('users');
			}
		});
		
		// Gather our inherited roles, their permissions, and our permissions.
		$query->with('permissions');
		
		$query->orderBy('weight');
		
		// Gather our inherited roles, their permissions, and our permissions.
		// Execute query
		$query->chunk(100, function($roles) use ($routes, $parentRoles, $userRoles, &$permissions) {
			RoleCache::addRolesToPermissions($roles, $routes, $parentRoles, $userRoles, $permissions);
		});
		
		return $permissions;
	}
	
	/**
	 * Returns a complete array of all possible routes and what roles belong to them.
	 *
	 * @return array
	 */
	protected function getPermissionRoutes()
	{
		// When building a permission mask, there are two main branches we can take.
		// "Normal", and "Unaccountable".
		// 
		// When the permission mask is finalized, it will still have these two branches.
		// But, depending on the user's conditions, it may have alternate routes within.
		// 
		// This is set up with the hope that we will be easily able to change how permission
		// masks are build in the future. Keep in mind that the masks's individual weights
		// still matter when determining what the user can actually do.
		
		$routes = [
			'normal'        => [],
			'unaccountable' => [],
		];
		
		// Both branches base off anonymous.
		$routes['normal'][]        = "anonymous";
		$routes['unaccountable'][] = "anonymous";
		
		// The unaccountable branch uses a special role.
		// This would generally be for Tor users.
		$routes['unaccountable'][] = "unaccountable";
		
		// Finally, if the user is registered, we add another role.
		// This is a bit of a placeholder. There are no permissions
		// by default that only affect registered users.
		if (!$this->isAnonymous())
		{
			$routes['normal'][]        = "registered";
			$routes['unaccountable'][] = "registered";
		}
		
		// All users are beholden to the absolute role.
		$routes['normal'][]        = "absolute";
		$routes['unaccountable'][] = "absolute";
		
		return $routes;
	}
	
	/**
	 * Return the user's entire permission object,
	 * build it if nessecary.
	 *
	 * @param  string  $board_uri
	 * @param  string  $route
	 * @return array
	 */
	protected function getPermissionsWithRoutes($board_uri = null, $route = null)
	{
		if (!isset($this->permissions))
		{
			$this->permissions = [];
		}
		
		if (!isset($this->permissions[$route][$board_uri]))
		{
			$cache = RoleCache::firstOrNew([
				'user_id'   => !$this->isAnonymous() ? $this->user_id : null,
				'board_uri' => is_null($board_uri)  ? null : $board_uri,
			]);
			
			if (!$cache->exists)
			{
				$value = $this->getPermissionMask($board_uri);
				$cache->value = json_encode($value);
				$cache->save();
			}
			else
			{
				$value = json_decode($cache->value, true);
			}
			
			$this->permissions = array_merge_recursive($this->permissions, $value);
			
			if (!isset($this->permissions[$route][$board_uri]))
			{
				$this->permissions[$route][$board_uri] = [];
			}
			
		}
		
		if (!is_null($route))
		{
			if (isset($this->permissions[$route][$board_uri]))
			{
				return $this->permissions[$route][$board_uri];
			}
			
			return [];
		}
		
		return $this->permissions;
	}
	
	/**
	 * Returns a collection of roles directly assigned to this user.
	 *
	 * @return \Illuminate\Support\Collection
	 */
	public function getRoles()
	{
		if ($this->isAnonymous())
		{
			return new Collection();
		}
		
		return $this->roles()->get();
	}
	
	/**
	 * Returns a human-readable username HTML string with a profile link.
	 *
	 * @return string  HTML
	 */
	public function getUsernameHTML()
	{
		if ($this->isAnonymous())
		{
			return "<span class=\"username\">Anonymous</span>";
		}
		
		return "<a href=\"{$this->getUserURL()}\" class=\"username\">{$this->username}</a>";
	}
	
	/**
	 * Returns a link to the user's public profile page.
	 *
	 * @return string  URL
	 */
	public function getUserURL()
	{
		return "/cp/user/{$this->username}.{$this->user_id}/";
	}
	
	/**
	 * Returns a human-readable IP address based on user permissions.
	 * This will obfuscate it if we do not have permission to view raw IPs.
	 *
	 * @param  string|CIDR  $ip  Normal IP string or a CIDR support object.
	 * @return string  Either $ip or an ip_less version.
	 */
	public function getTextForIP($ip)
	{
		if ($this->canViewRawIP())
		{
			return (string) $ip;
		}
		
		if ($ip instanceof CIDR && $ip->getStart() != $ip->getEnd())
		{
			return ip_less($ip->getStart()) . "/" . $ip->getPrefix();
		}
		
		
		return ip_less($ip);
	}
	
	/**
	 * Setter for the accountable mask.
	 *
	 * @param  bool  $accountable
	 * @return bool
	 */
	public function setAccountable($accountable)
	{
		$this->accountable = !!$accountable;
	}
	
}
