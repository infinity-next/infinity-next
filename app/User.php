<?php

namespace App;

use App\Auth\Permittable;
use App\Contracts\Auth\Permittable as PermittableContract;
use App\Contracts\Support\Sluggable as SluggableContract;
use App\Traits\PermissionUser;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Str;

/**
 * Model representing static page content for boards and the global site.
 *
 * @category   Model
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class User
extends Model
implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract,
    PermittableContract
{
    use Authenticatable,
        Authorizable,
        CanResetPassword,
        Permittable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'password_legacy',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'username',
        'email',
        'email_verified',
        'password',
        'password_legacy',
        'remember_token',
        'stripe_active',
        'stripe_id',
        'stripe_subscription',
        'stripe_plan',
        'last_four',
        'trial_ends_at',
        'subscription_ends_at',
        'subscription_kill_token',
        'braintree_active',
        'braintree_id',
    ];

    /**
     * Ties database triggers to the model.
     */
    public static function boot()
    {
        parent::boot();

        // Setup event bindings...

        // When creating a user, make empty email fields into NULL.
        static::creating(function ($user) {
            if ($user->email == '') {
                $user->email = null;
            }

            return true;
        });
    }

    public function boards()
    {
        return $this->hasMany(Board::class, 'operated_by', 'user_id');
    }

    public function createdBoards()
    {
        return $this->hasMany(Board::class, 'created_by', 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    /**
     * Supplies the hashed password for this user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        $hash = $this->password;

        if (is_null($hash)) {
            $legacyData = json_decode($this->password_legacy);

            return $legacyData->hash;
        }

        return $hash;
    }

    /**
     * Returns a hasher based on password data, allowing support for old encryption types.
     *
     * @return \Illuminate\Contracts\Hashing\Hasher|false
     */
    public function getAuthObject()
    {
        $hasher = false;

        if (!is_null($this->password_legacy)) {
            $legacyData = json_decode($this->password_legacy);
            $legacyHasher = "App\\Services\\Hashing\\{$legacyData->hasher}Hasher";

            $hasher = new $legacyHasher();

            foreach ($legacyData as $option => $value) {
                $hasher->{$option} = $value;
            }
        }

        return $hasher;
    }

    /**
     * Fetches all reports that this user can view (not submitted reports).
     *
     * @return Collection
     */
    public function getReportedPostsViewable()
    {
        $posts = Post::whereHasReportsFor($this)
            ->withEverything()
            ->get();

        foreach ($posts as $post) {
            foreach ($post->reports as $report) {
                $report->setRelation('post', $post);
            }
        }

        return $posts;
    }

    /**
     * Returns roles which belong to a specific board.
     *
     * @param  \App\Board  $board
     *
     * @return Collection
     */
    public function getBoardRoles(Board $board)
    {
        return $this->roles()->where('board_uri', $board->board_uri)->get();
    }

    /**
     * Returns a fully qualified URL for a route on this user.
     *
     * @param  string  $route  Optional route addendum.
     * @param  array  $params  Optional array of parameters to be added.
     * @param  bool  $abs  Options indicator if the URL is to be absolute.
     *
     * @return string
     */
    public function getUrl($route = "show", array $params = [], $abs = true)
    {
        return route(
            implode('.', array_filter([
                "panel",
                "user",
                $route,
            ])),
            [
                'slug' => $this->getSlug(),
                'user' => $this,
            ] + $params,
            true
        );
    }

    /**
     * Returns a fully qualified URL for a route to user's board staff page.
     *
     * @param  \App\Board  $board
     * @param  string  $route  Optional route addendum.
     * @param  array  $params  Optional array of parameters to be added.
     * @param  bool  $abs  Options indicator if the URL is to be absolute.
     *
     * @return string
     */
    public function getBoardStaffUrl(Board $board, $route = "show", array $params = [], $abs = true)
    {
        return $board->getPanelUrl(
            implode('.', array_filter([
                "staff",
                $route,
            ])),
            [
                'user' => $this
            ] + $params,
            $abs
        );
    }

    /**
     * Returns part of the URL that would be used to identify this user.
     *
     * @return string
     */
    public function getSlug()
    {
        return Str::slug($this->username, '-');
    }

    /**
     * Query where has admin role.
     *
     * @return Query
     */
    public function scopeWhereAdmin($query)
    {
        return $query->whereHas('roles', function ($query) {
            $tempInstance = with(new Role());
            $directSelect = $tempInstance->getTable();
            $directKey = $tempInstance->getKeyName();

            //$query->where(\DB::raw("`{$directSelect}`.`{$directKey}`"), '=', Role::ID_ADMIN);
            $query->where("{$directSelect}.{$directKey}", '=', Role::ID_ADMIN);
        });
    }

    /**
     * Query where has admin role.
     *
     * @return Query
     */
    public function scopeWhereOwner($query)
    {
        return $query->whereHas('roles', function ($query) {
            $tempInstance = with(new Role());
            $directSelect = $tempInstance->getTable();
            $directKey = $tempInstance->getKeyName();

            $query->where('role', 'owner');
        });
    }

    /**
     * Query by username.
     *
     * @param string $username
     *
     * @return Query
     */
    public function scopeWhereUsername($query, $username)
    {
        return $query->where('username', '=', $username)->limit(1);
    }
}
