<?php

namespace App;

use App\Board;
use App\Post;
use App\User;
use App\Contracts\Auth\Permittable;
use App\Support\IP;
use App\Traits\EloquentBinary;
use Illuminate\Database\Eloquent\Model;
use View;

class Report extends Model
{
    use EloquentBinary;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'reports';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'report_id';

    /**
     * Attributes which do not exist but should be appended to the JSON output.
     *
     * @var array
     */
    protected $appends = [
        'html',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'reason' => "string",
        'board_uri' => "string",
        'post_id' => "int",
        'reporter_ip' => "ip",
        'user_id' => "int",
        'is_dismissed' => "bool",
        'is_successful' => "bool",
        'global' => "bool",
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'promoted_at' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reason',
        'board_uri',
        'post_id',
        'reporter_ip',
        'user_id',
        'is_dismissed',
        'is_successful',
        'global',
        'promoted_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'reporter_ip',
        'user_id',

        'board',
        'post',
        'user',
    ];

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_uri');
    }

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Returns the fully rendered HTML of a post in the JSON output.
     *
     * @return string
     */
    public function getHtmlAttribute()
    {
        return View::make('content.panel.report.item', [
            'report' => $this,
            'reportedPost' => $this->post,
        ])->render();
    }

    /**
     * Returns a fully qualified URL.
     *
     * @param  string  $route  Optional route addendum.
     * @param  array  $params  Optional array of parameters to be added.
     * @param  bool  $abs  Options indicator if the URL is to be absolute.
     *
     * @return string
     */
    public function getUrl($route = "index", array $params = [], $abs = true)
    {
        return route(
            implode(array_filter([
                "panel",
                "reports",
                $route,
            ]), '.'),
            [
                'report' => $this,
            ] + $params,
            true
        );
    }

    /**
     * Returns a fully qualified URL for post bulk actions.
     *
     * @param  string  $route  Optional route addendum.
     * @param  array  $params  Optional array of parameters to be added.
     * @param  bool  $abs  Options indicator if the URL is to be absolute.
     *
     * @return string
     */
    public function getPostUrl($route = "index", array $params = [], $abs = true)
    {
        return route(
            implode(array_filter([
                "panel",
                "reports",
                $route,
                "post",
            ]), '.'),
            [
                'post' => $this->post,
            ] + $params,
            true
        );
    }

    /**
     * Determines if the post has been Demoted.
     *
     * @return bool
     */
    public function isDemoted()
    {
        return !$this->global && !is_null($this->promoted_at);
    }

    /**
     * Determines if the post is still open.
     *
     * @return bool
     */
    public function isOpen()
    {
        return !$this->is_dismissed && !$this->is_successful;
    }

    /**
     * Determines if the post has been promoted.
     *
     * @return bool
     */
    public function isPromoted()
    {
        return $this->global && !is_null($this->promoted_at);
    }

    /**
     * Refines query to only reports by this user or by this IP.
     *
     * @param  \App\Contracts\Auth\Permittable  $user
     */
    public function scopeWhereByIpOrUser($query, Permittable $user)
    {
        $query->where(function ($query) use ($user) {
            $query->where('reporter_ip', new IP);

            if (!$user->isAnonymous()) {
                $query->orWhere('user_id', $user->user_id);
            }
        });
    }

    /**
     * Reduces query to only reports that require action.
     */
    public function scopeWhereOpen($query)
    {
        return $query->where(function ($query) {
            $query->where('is_dismissed', false);
            $query->where('is_successful', false);
        });
    }

    /**
     * Reduces query to only reports which have been elevated by local staff.
     */
    public function scopeWherePromoted($query)
    {
        return $query->where('promoted_at');
    }

    /**
     * Reduced query to only reports that the user is directly responsible for.
     * This means 'site.reports' open `global` ONLY and 'board.reports' only matter in direct assignment.
     *
     * @param  \App\Contracts\Auth\Permittable  $user
     */
    public function scopeWhereResponsibleFor($query, Permittable $user)
    {
        return $query->where(function ($query) use ($user) {
            $query->whereIn('board_uri', $user->canInBoards('board.reports'));

            if (!$user->can('site.reports')) {
                $query->where('global', false);
            }
            else {
                $query->orWhere('global', true);
            }
        });
    }
}
