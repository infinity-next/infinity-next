<?php

// use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use App\Permission;
use App\Role;
use App\User;

class PermissionsTest extends TestCase
{
	/**
	 * An admin user.
	 *
	 * @var App\User
	 */
	protected $admin;
	
	/**
	 * The admin role.
	 *
	 * @var App\Role
	 */
	protected $adminRole;
	
	/**
	 * A board owner.
	 *
	 * @var App\User
	 */
	protected $owner;
	
	/**
	 * The board owner role.
	 *
	 * @var App\Role
	 */
	protected $ownerRole;
	
	/**
	 * All permissions.
	 *
	 * @var Collection of App\Permission
	 */
	protected $permissions;
	
	
	/**
	 * Default preparation for each test
	 */
	public function setUp()
	{
		parent::setUp();
		
		// Fetch the admin user.
		$this->admin = User::whereAdmin()
			->with('roles')
			->with('roles.permissions')
			->take(1)
			->get()
			->first();
		
		// Fetch the admin role.
		$this->adminRole = Role::where('role_id', '=', Role::ID_ADMIN)->with('permissions')->first();
		
		// Fetch a board owner.
		$this->owner = User::whereOwner()
			->with('roles')
			->with('roles.permissions')
			->take(1)
			->get()
			->first();
		
		// Fetch the owner role.
		$this->ownerRole = $this->owner->roles->where('role', "owner")->first();
		
		// Fetch all permissions.
		$this->permissions = Permission::get();
	}
	
	/**
	 * Attempts to find a root user and its role.
	 *
	 * @return void
	 */
	public function testAdminModel()
	{
		// Assert is a user.
		$this->assertInstanceOf("App\User", $this->admin);
		// Assert has roles.
		$this->assertTrue( count($this->admin->roles) > 0 );
		
		
		// Assert role is a role.
		$this->assertInstanceOf("App\Role", $this->adminRole);
		// Assert role has permissions.
		$this->assertTrue( count($this->adminRole->permissions) > 0 );
		// Assert that our admin role is the hard-coded admin ID.
		$this->assertEquals( Role::ID_ADMIN, $this->adminRole->role_id );
	}
	
	/**
	 * Assets various permissions that a root admin user should always be able to carry out. 
	 *
	 * @return void
	 */
	public function testAdminPermissions()
	{
		// Assert root can edit config.
		$this->assertTrue( $this->admin->canAdminConfig() );
		// Assert root can post in any board.
		$this->assertTrue( $this->admin->canPostThread() );
	}
	
	/**
	 * Attempts to find all permissions.
	 *
	 * @return void
	 */
	public function testPermissionModel()
	{
		// Assert we have permissions.
		$this->assertTrue( count($this->permissions) > 0 );
		// Assert we have a number of perrmisions that equal our admin's.
		$this->assertEquals( count($this->adminRole->permissions), count($this->adminRole->permissions) );
	}
	
	/**
	 * Attempts to find a board owner and its role.
	 *
	 * @return void
	 */
	public function testOwnerModel()
	{
		// Assert user is a user.
		$this->assertInstanceOf("App\User", $this->owner);
		// Assert has roles.
		$this->assertTrue( count($this->owner->roles) > 0 );
		
		
		// Assert role is a role.
		$this->assertInstanceOf("App\Role", $this->ownerRole);
		// Assert that our owner role has an inherited role ID of the hard-coded owner id.
		$this->assertEquals( Role::ID_OWNER, $this->ownerRole->inherit_id );
	}
	
}
