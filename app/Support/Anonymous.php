<?php namespace App\Support;

/**
 *
 * The Anonymous class is designed to be a non-inheriting mockery of the
 * User model class. The User represents a Database relationship while
 * utilizing a trait to check permissions and status. The Anonymous class
 * has no association or bearing on the database but will answer questions
 * posed by the application in its place.
 *
 */

use App\Board;
use App\Post;
use App\Contracts\PermissionUser as PermissionUserContract;
use App\Traits\PermissionUser;

class Anonymous implements PermissionUserContract
{
	use PermissionUser;
	
	/**
	 * Dummy properties for User models.
	 *
	 * @var mixed
	 */
	public $user_id = null;
	
	/**
	 * Distinguishes this model from an Anonymous user.
	 *
	 * @var boolean
	 */
	protected $anonymous = true;
}