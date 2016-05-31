<?php

namespace App;

use App\Contracts\PermissionUser;
use App\Support\IP;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use \App\Traits\EloquentBinary;

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
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['reason', 'board_uri', 'post_id', 'reporter_ip', 'user_id', 'is_dismissed', 'is_successful', 'global'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['reporter_ip', 'user_id'];

    public function board()
    {
        return $this->belongsTo('\App\Board', 'board_uri');
    }

    public function post()
    {
        return $this->belongsTo('\App\Post', 'post_id');
    }

    public function user()
    {
        return $this->belongsTo('\App\User', 'user_id');
    }

    /**
     * Determines if a user can view this report in any context.
     * This does not determine if a report is useful in a management view.
     *
     * @param PermissionUser $user
     *
     * @return bool
     */
    public function canView(PermissionUser $user)
    {
        if (is_null($this->board_uri) && $user->canViewReportsGlobally()) {
            return true;
        }

        if (!is_null($this->board_uri) && $user->canViewReports($this->board_uri)) {
            return true;
        }

        return false;
    }

    /**
     * Determines if the user can Demote the post.
     *
     * @param PermissionUser $user
     *
     * @return bool
     */
    public function canDemote(PermissionUser $user)
    {
        return !$this->isDemoted() && $this->global;
    }

    /**
     * Determines if the user can dismiss the report.
     *
     * @param PermissionUser $user
     *
     * @return bool
     */
    public function canDismiss(PermissionUser $user)
    {
        // At the moment, anyone who can view can dismiss.
        return $this->canView($user);
    }

    /**
     * Determines if the user can promote the post.
     *
     * @param PermissionUser $user
     *
     * @return bool
     */
    public function canPromote(PermissionUser $user)
    {
        return $user->canReportGlobally($this->post) && !$this->isPromoted() && !$this->global;
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
     * Returns the reporter's IP in a human-readable format.
     *
     * @return string
     */
    public function getReporterIpAsString()
    {
        return (new IP($this->reporter_ip))->toText();
    }

    /**
     * Gets our binary value and unwraps it from any stream wrappers.
     *
     * @param mixed $value
     *
     * @return IP
     */
    public function getReporterIpAttribute($value)
    {
        return new IP($value);
    }

    /**
     * Sets our binary value and encodes it if required.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function setReporterIpAttribute($value)
    {
        $this->attributes['reporter_ip'] = (new IP($value))->toSQL();
    }

    /**
     * Refines query to only reports by this user or by this IP.
     */
    public function scopeWhereByIPOrUser($query, PermissionUser $user)
    {
        $query->where(function ($query) use ($user) {
            $query->where('reporter_ip', new IP());

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
     * @param PermissionUser $user
     */
    public function scopeWhereResponsibleFor($query, PermissionUser $user)
    {
        return $query->where(function ($query) use ($user) {
            $query->whereIn('board_uri', $user->canInBoards('board.reports'));

            if (!$user->can('site.reports')) {
                $query->where('global', false);
            } else {
                $query->orWhere('global', true);
            }
        });
    }
}
