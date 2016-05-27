<?php

namespace App\Support;

/*
 *
 * The Anonymous class is designed to be a non-inheriting mockery of the
 * User model class. The User represents a Database relationship while
 * utilizing a trait to check permissions and status. The Anonymous class
 * has no association or bearing on the database but will answer questions
 * posed by the application in its place.
 *
 */

use App\Contracts\PermissionUser as PermissionUserContract;
use App\Traits\PermissionUser;
//use Laravel\Cashier\Billable;
//use Laravel\Cashier\Contracts\Billable as BillableContract;
use InfinityNext\Braintree\Billable;
use InfinityNext\Braintree\Contracts\Billable as BillableContract;

class Anonymous implements BillableContract, PermissionUserContract
{
    use Billable, PermissionUser;

    /**
     * Dummy properties for User models.
     *
     * @var mixed
     */
    public $user_id = null;
    public $username = null;
    public $email = null;


    /**
     * Dummy properties for anonymous donations.
     *
     * @var mixed
     */
    public $braintree_active = null;
    public $braintree_id = null;
    public $stripe_active = null;
    public $stripe_id = null;
    public $stripe_subscription = null;
    public $stripe_plan = null;
    public $last_four = null;
    public $trial_ends_at = null;
    public $subscription_ends_at = null;
    public $subscription_kill_token = null;

    /**
     * Distinguishes this model from an Anonymous user.
     *
     * @var bool
     */
    protected $anonymous = true;

    /**
     * Allow Stripe to run properly.
     *
     * @var App\Support\Anonymous
     */
    public function save()
    {
        return $this;
    }
}
