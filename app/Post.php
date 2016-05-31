<?php

namespace App;

use Acetone;
use App\Contracts\PermissionUser;
use App\Contracts\Support\Formattable as FormattableContract;
use App\Services\ContentFormatter;
use App\Support\Formattable;
use App\Support\Geolocation;
use App\Support\IP;
use App\Traits\TakePerGroup;
use App\Traits\EloquentBinary;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cache;
use DB;
use Input;
use File;
use Request;
use Event;
use App\Events\ThreadNewReply;

/**
 * Model representing posts and threads for boards.
 *
 * @category   Model
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class Post extends Model implements FormattableContract
{
    use Formattable;
    use EloquentBinary;
    use TakePerGroup;
    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'posts';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'post_id';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'board_id' => 'int',
        'reply_to' => 'int',
        'author_ip' => 'ip',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'board_uri',
        'board_id',
        'reply_to',
        'reply_to_board_id',
        'reply_count',
        'reply_file_count',
        'reply_last',
        'bumped_last',

        'created_at',
        'updated_at',
        'stickied',
        'stickied_at',
        'bumplocked_at',
        'locked_at',
        'featured_at',

        'author_ip',
        'author_ip_nulled_at',
        'author_id',
        'author_country',
        'capcode_id',
        'subject',
        'author',
        'insecure_tripcode',
        'email',
        'password',
        'flag_id',

        'body',
        'body_has_content',
        'body_too_long',
        'body_parsed',
        'body_parsed_preview',
        'body_parsed_at',
        'body_html',
        'body_rtl',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        // Post Items
        'author_ip',
        'password',
        'body',
        'body_parsed',
        'body_parsed_at',
        'body_html',

        // Relationships
        // 'bans',
        'board',
        // 'citedBy',
        // 'citedPosts',
        // 'editor',
        'op',
        // 'replies',
        // 'reports',
    ];

    /**
     * Attributes which do not exist but should be appended to the JSON output.
     *
     * @var array
     */
    protected $appends = [
        'content_raw',
        'content_html',
        'recently_created',
    ];

    /**
     * Attributes which are automatically sent through a Carbon instance on load.
     *
     * @var array
     */
    protected $dates = [
        'reply_last',
        'bumped_last',
        'created_at',
        'updated_at',
        'deleted_at',
        'stickied_at',
        'bumplocked_at',
        'locked_at',
        'body_parsed_at',
        'author_ip_nulled_at',
    ];

    public function attachments()
    {
        return $this->belongsToMany("\App\FileStorage", 'file_attachments', 'post_id', 'file_id')
            ->withPivot('attachment_id', 'filename', 'is_spoiler', 'is_deleted', 'position');
    }

    public function attachmentLinks()
    {
        return $this->hasMany("\App\FileAttachment");
    }

    public function backlinks()
    {
        return $this->hasMany('\App\PostCite', 'cite_id', 'post_id');
    }

    public function bans()
    {
        return $this->hasMany('\App\Ban', 'post_id');
    }

    public function board()
    {
        return $this->belongsTo('\App\Board', 'board_uri');
    }

    public function capcode()
    {
        return $this->hasOne('\App\Role', 'role_id', 'capcode_id');
    }

    public function cites()
    {
        return $this->hasMany('\App\PostCite', 'post_id');
    }

    public function citedPosts()
    {
        return $this->belongsToMany("\App\Post", 'post_cites', 'post_id');
    }

    public function citedByPosts()
    {
        return $this->belongsToMany("\App\Post", 'post_cites', 'cite_id', 'post_id');
    }

    public function editor()
    {
        return $this->hasOne('\App\User', 'user_id', 'updated_by');
    }

    public function flag()
    {
        return $this->hasOne('\App\BoardAsset', 'board_asset_id', 'flag_id');
    }

    public function op()
    {
        return $this->belongsTo('\App\Post', 'reply_to', 'post_id');
    }

    public function replies()
    {
        return $this->hasMany('\App\Post', 'reply_to', 'post_id');
    }

    public function replyFiles()
    {
        return $this->hasManyThrough('App\FileAttachment', 'App\Post', 'reply_to', 'post_id');
    }

    public function reports()
    {
        return $this->hasMany('\App\Report', 'post_id');
    }

    /**
     * Determines if the user can bumplock this post.
     *
     * @param App\Contracts\PermissionUser $user
     *
     * @return bool
     */
    public function canBumplock($user)
    {
        return $user->canBumplock($this);
    }

    /**
     * Determines if the user can delete this post.
     *
     * @param App\Contracts\PermissionUser $user
     *
     * @return bool
     */
    public function canDelete($user)
    {
        return $user->canDelete($this);
    }

    /**
     * Determines if the user can edit this post.
     *
     * @param App\Contracts\PermissionUser $user
     *
     * @return bool
     */
    public function canEdit($user)
    {
        return $user->canEdit($this);
    }

    /**
     * Determines if the user can lock this post.
     *
     * @param App\Contracts\PermissionUser $user
     *
     * @return bool
     */
    public function canLock($user)
    {
        return $user->canLock($this);
    }

    /**
     * Determines if the user can reply to post, or if this thread is open to replies in general.
     *
     * @param App\Contracts\PermissionUser|null $user
     *
     * @return bool
     */
    public function canReply($user = null)
    {
        if (!is_null($user)) {
            return $user->canReply($this);
        }

        return true;
    }

    /**
     * Determines if the user can report this post to board owners.
     *
     * @param App\Contracts\PermissionUser $user
     *
     * @return bool
     */
    public function canReport($user)
    {
        return $user->canReport($this);
    }

    /**
     * Determines if the user can report this post to site owners.
     *
     * @param App\Contracts\PermissionUser $user
     *
     * @return bool
     */
    public function canReportGlobally($user)
    {
        return $user->canReportGlobally($this);
    }

    /**
     * Determines if the user can sticky or unsticky this post.
     *
     * @param App\Contracts\PermissionUser $user
     *
     * @return bool
     */
    public function canSticky($user)
    {
        return $user->canSticky($this);
    }

    /**
     * Counts the number of currently related reports that can be promoted.
     *
     * @param PermissionUser $user
     *
     * @return int
     */
    public function countReportsCanPromote(PermissionUser $user)
    {
        $count = 0;

        foreach ($this->reports as $report) {
            if ($report->canPromote($user)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Counts the number of currently related reports that can be demoted.
     *
     * @param PermissionUser $user
     *
     * @return int
     */
    public function countReportsCanDemote(PermissionUser $user)
    {
        $count = 0;

        foreach ($this->reports as $report) {
            if ($report->canDemote($user)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Checks a supplied password against the set one.
     *
     * @param string $password
     *
     * @return bool
     */
    public function checkPassword($password)
    {
        $hash = $this->makePassword($password, false);

        return !is_null($hash) && !is_null($this->password) && password_verify($hash, $this->password);
    }

    /**
     * Removes post HTML caches..
     */
    public function clearPostHTMLCache()
    {
        switch (env('CACHE_DRIVER')) {
            case 'file':
            case 'database':
                break;

            default:
                Cache::tags(["post_{$this->post_id}"])->flush();
                break;
        }
    }

    /**
     * Removes thread caches containing this post.
     */
    public function clearThreadCache()
    {
        // If this post is a reply to a thread
        if ($this->reply_to_board_id) {
            switch (env('CACHE_DRIVER')) {
                case 'file':
                case 'database':
                    Cache::forget("board.{$this->board_uri}.thread.{$this->reply_to_board_id}");
                    break;

                default:
                    Cache::tags(["board.{$this->board_uri}", 'threads'])->forget("board.{$this->board_uri}.thread.{$this->reply_to_board_id}");
                    break;
            }
        }

        switch (env('CACHE_DRIVER')) {
            case 'file':
            case 'database':
                Cache::forget("board.{$this->board_uri}.thread.{$this->board_id}");
                break;

            default:
                Cache::tags(["board.{$this->board_uri}", 'threads'])->forget("board.{$this->board_uri}.thread.{$this->board_id}");
                break;
        }

        if (env('APP_VARNISH')) {
            Acetone::purge("/{$this->board_uri}/thread/{$this->reply_to_board_id}");
        }
    }

    /**
     * Returns backlinks for this post which are permitted by board config.
     *
     * @param \App\Board|null $board Optional. Board to check against. If null, assumes this post's board.
     *
     * @return Collection of \App\PostCite
     */
    public function getAllowedBacklinks(Board $board = null)
    {
        if (is_null($board)) {
            $board = $this->board;
        }

        $backlinks = collect();

        foreach ($this->backlinks as $backlink) {
            if ($board->isBacklinkAllowed($backlink)) {
                $backlinks->push($backlink);
            }
        }

        return $backlinks;
    }

    /**
     * Returns a small, unique code to identify an author in one thread.
     *
     * @return string
     */
    public function makeAuthorId()
    {
        if ($this->author_ip === null) {
            return '000000';
        }

        $hashParts = [];
        $hashParts[] = env('APP_KEY');
        $hashParts[] = $this->board_uri;
        $hashParts[] = $this->reply_to_board_id ?: $this->board_id;
        $hashParts[] = $this->author_ip;

        $hash = implode($hashParts, '-');
        $hash = hash('sha256', $hash);
        $hash = substr($hash, 12, 6);

        return $hash;
    }

    /**
     * Returns a SHA1 hash (in text or binary) representing an originality/r9k checksum.
     *
     * @static
     *
     * @param string $body   The body to be checksum'd.
     * @param bool   $binary Optional. If the return should be binary. Defaults false.
     *
     * @return string|binary
     */
    public static function makeChecksum($text, $binary = false)
    {
        $postRobot = preg_replace('/\s+/', '', $text);
        $checksum = sha1($postRobot, $binary);

        if ($binary) {
            return binary_sql($checksum);
        }

        return $checksum;
    }

    /**
     * Bcrypts a password using relative information.
     *
     * @param string $password The password to be set. If empty password is given, no password will be set.
     * @param bool   $encrypt  Optional. Indicates if the hash should be bcrypted. Defaults true.
     *
     * @return string
     */
    public function makePassword($password = null, $encrypt = true)
    {
        $hashParts = [];

        if ((bool) $password) {
            $hashParts[] = env('APP_KEY');
            $hashParts[] = $this->board_uri;
            $hashParts[] = $password;
            $hashParts[] = $this->board_id;
        }

        $parts = implode($hashParts, '|');

        if ($encrypt) {
            return bcrypt($parts);
        }

        return $parts;
    }

    /**
     * Turns the author id into a consistent color.
     *
     * @param bool $asArray
     *
     * @return string In the format of rgb(xxx,xxx,xxx) or as an array.
     */
    public function getAuthorIdBackgroundColor($asArray = false)
    {
        $authorId = $this->author_id;
        $colors = [];
        $colors[] = crc32(substr($authorId, 0, 2)) % 254 + 1;
        $colors[] = crc32(substr($authorId, 2, 2)) % 254 + 1;
        $colors[] = crc32(substr($authorId, 4, 2)) % 254 + 1;

        if ($asArray) {
            return $colors;
        }

        return 'rgba('.implode(',', $colors).',0.75)';
    }

    /**
     * Takess the author id background color and determines if we need a white or black text color.
     *
     * @return string In the format of rgba(xxx,xxx,xxx,x)
     */
    public function getAuthorIdForegroundColor()
    {
        $colors = $this->getAuthorIdBackgroundColor(true);

        if (array_sum($colors) < 382) {
            return 'rgb(255,255,255)';
        }

        foreach ($colors as $color) {
            if ($color > 200) {
                return 'rgb(0,0,0)';
            }
        }

        return 'rgb(0,0,0)';
    }

    /**
     * Returns the raw input for a post for the JSON output.
     *
     * @return string
     */
    public function getAuthorIdAttribute()
    {
        if ($this->board->getConfig('postsThreadId', false)) {
            return $this->attributes['author_id'];
        }

        return;
    }

    /**
     * Language direction of this post.
     *
     * @return string|null
     */
    public function getBodyDirectionAttr()
    {
        $rtl = $this->body_rtl;

        if (is_null($rtl)) {
            return '';
        }

        return 'dir="'.($rtl ? 'rtl' : 'ltr').'"';
    }

    /**
     * Returns the fully rendered HTML content of this post.
     *
     * @param bool $skipCache
     *
     * @return string
     */
    public function getBodyFormatted($skipCache = false)
    {
        if (!$skipCache) {
            // Markdown parsed content
            if (!is_null($this->body_html)) {
                if (!mb_check_encoding($this->body_html, 'UTF-8')) {
                    return '<tt style="color:red;">Invalid encoding. This should never happen!</tt>';
                }

                return $this->body_html;
            }

            // Raw HTML input
            if (!is_null($this->body_parsed)) {
                return $this->body_parsed;
            }
        }

        $ContentFormatter = new ContentFormatter();
        $this->body_too_long = false;
        $this->body_parsed = $ContentFormatter->formatPost($this);
        $this->body_parsed_preview = null;
        $this->body_parsed_at = $this->freshTimestamp();
        $this->body_has_content = $ContentFormatter->hasContent();
        $this->body_rtl = $ContentFormatter->isRtl();

        if (!mb_check_encoding($this->body_parsed, 'UTF-8')) {
            return '<tt style="color:red;">Invalid encoding. This should never happen!</tt>';
        }


        // If our body is too long, we need to pull the first X characters and do that instead.
        // We also set a token indicating this post has hidden content.
        if (mb_strlen($this->body) > 1200) {
            $this->body_too_long = true;
            $this->body_parsed_preview = $ContentFormatter->formatPost($this, 1000);
        }

        // We use an update here instead of just saving $post because, in this method
        // there will frequently be additional properties on this object that cannot
        // be saved. To make life easier, we just touch the object.
        static::where(['post_id' => $this->post_id])->update([
            'body_has_content' => $this->body_has_content,
            'body_too_long' => $this->body_too_long,
            'body_parsed' => $this->body_parsed,
            'body_parsed_preview' => $this->body_parsed_preview,
            'body_parsed_at' => $this->body_parsed_at,
            'body_rtl' => $this->body_rtl,
        ]);

        return $this->body_parsed;
    }

    /**
     * Returns a partially rendered HTML preview of this post.
     *
     * @param bool $skipCache
     *
     * @return string
     */
    public function getBodyPreview($skipCache = false)
    {
        $body_parsed = $this->getBodyFormatted($skipCache);

        if ($this->body_too_long !== true || !isset($this->body_parsed_preview)) {
            return $body_parsed;
        }

        return $this->body_parsed_preview;
    }

    /**
     * Returns the raw input for a post for the JSON output.
     *
     * @return string
     */
    public function getContentRawAttribute($value)
    {
        if (!$this->trashed() && isset($this->attributes['body'])) {
            return $this->attributes['body'];
        }

        return;
    }

    /**
     * Returns the rendered interior HTML for a post for the JSON output.
     *
     * @return string
     */
    public function getContentHtmlAttribute($value)
    {
        if (!$this->trashed() && isset($this->attributes['body'])) {
            return $this->getBodyFormatted();
        }

        return;
    }

    /**
     * Returns a name for the country. This is usually the ISO 3166-1 alpha-2 code.
     *
     * @return string|null
     */
    public function getCountryCode()
    {
        if (!is_null($this->author_country)) {
            if ($this->author_country == '') {
                return 'unknown';
            }

            return $this->author_country;
        }

        return;
    }

    /**
     * Returns the fully rendered HTML of a post in the JSON output.
     *
     * @return string
     */
    public function getHtmlAttribute()
    {
        if (!$this->trashed()) {
            return $this->toHTML(false, false, false);
        }

        return;
    }

    /**
     * Returns the recently created flag for the JSON output.
     *
     * @return string
     */
    public function getRecentlyCreatedAttribute()
    {
        return $this->wasRecentlyCreated;
    }

    /**
     * Returns a count of current reply relationships.
     *
     * @return int
     */
    public function getReplyCount()
    {
        return $this->getRelation('replies')->count();
    }

    /**
     * Returns a count of current reply relationships.
     *
     * @return int
     */
    public function getReplyFileCount()
    {
        $files = 0;

        foreach ($this->getRelation('replies') as $reply) {
            $files += $reply->getRelation('attachments')->count();
        }

        return $this->reply_file_count < $files ? $this->reply_file_count : max(0, $files);
    }

    /**
     * Returns a splice of the replies based on the 2channel style input.
     *
     * @param string $uri
     *
     * @return static|bool Returns $this with modified replies relationship, or false if input error.
     */
    public function getReplySplice($splice)
    {
        // Matches:
        // l50   OP and last 50 posts
        // l2    OP and last 2 posts
        // 600-  OP and all posts from 600 onwards
        // 10-20 OP and posts ten through twenty
        // 600   OP and post 600 only
        // -100  OP and first 100 posts
        // Indices start at 1, which includes OP.
        if (preg_match('/^(?<last>l)?(?<start>\d+)?(?P<between>-)?(?P<end>\d+)?$/', $splice, $m) === 1) {
            $count = $this->replies->count();
            $last = isset($m['last']) && $m['last'] == 'l' ? true : false;
            $start = isset($m['start']) && $m['start'] != '' ? (int) $m['start'] : false;
            $between = isset($m['between']) && $m['between'] == '-' ? true : false;
            $end = isset($m['end']) && $m['end'] != '' ? (int) $m['end']   : false;
            $length = null;

            // Fetching last posts?
            if ($last === true) {
                // Pull last X.
                if ($start !== false && $between == false && $end === false) {
                    $start = $count - $start;
                    $length = $count;
                } else {
                    return false;
                }
            }
            // Pull between two indices.
            elseif ($between === true) {
                // Have we specified an X-Y range?
                if ($start !== false && $end !== false) {
                    // Abort if we've specified an incorrect range.
                    if ($start <= 0 || $start > $end) {
                        return false;
                    }

                    $start -= 2;
                    $length = $end - $start - 1;
                }
                // Have we specified a -X (pull first X posts) range?
                elseif ($start === false && $end !== false) {
                    $start = 0;
                    $length = $end - 1;

                    if ($length < 0) {
                        return false;
                    }
                }
                // Have we specified a X- (pull from post X up) range?
                elseif ($start !== false && $end === false) {
                    $start -= 2;
                    $length = $count;
                } else {
                    return false;
                }
            }
            // Pull a single post.
            elseif ($start !== false) {
                if ($start > 1) {
                    $length = 1;
                }
                // If we're requesting OP, we want no children.
                elseif ($start == 1) {
                    $length = 0;
                } else {
                    return false;
                }
            } else {
                return false;
            }

            $start = max($start, 0);

            return $this->setRelation('replies', $this->replies->splice($start, $length));
        }

        return false;
    }

    /**
     * Returns a relative URL for an API route to this post.
     *
     * @param string $route Optional route addendum.
     * @param array $params Optional array of parameters to be added.
     * @param bool $abs Options indicator if the URL is to be absolute.
     *
     * @return string
     */
    public function getApiUrl($route = "index", array $params = [], $abs = false)
    {
        if ($this->reply_to_board_id) {
            $url_id = $this->reply_to_board_id;
        } else {
            $url_id = $this->board_id;
        }

        return route(
            implode(array_filter([
                'api',
                'board',
                $route,
            ]), '.'),
            [
                'post_id'=> $url_id,
                'board'  => $this->board,
            ] + $params,
            $abs
        );
    }

    /**
     * Returns a relative URL for opening this post.
     *
     * @return string
     */
    public function getUrl($splice = null)
    {
        $url_hash = "";

        if ($this->reply_to_board_id) {
            $url_id = $this->reply_to_board_id;
            $url_hash = "#{$this->board_id}";
        } else {
            $url_id = $this->board_id;
        }

        return route('board.thread', [
            'board_uri' => $this->board_uri,
            'post_id' => $url_id,
            'splice' => $splice,
        ], false).$url_hash;
    }

    /**
     * Returns a relative URL for replying to this post.
     *
     * @return string
     */
    public function getReplyUrl($splice = null)
    {
        if ($this->reply_to_board_id) {
            $url_id = $this->reply_to_board_id;
        } else {
            $url_id = $this->board_id;
        }

        return route('board.thread', [
            'board_uri' => $this->board_uri,
            'post_id' => $url_id,
            'splice' => $splice,
        ], false)."#reply-{$this->board_id}";
    }

    /**
     * Returns a post moderation URL for this post.
     *
     * @return string
     */
    public function getModUrl($route = "index", array $params = [], $abs = false)
    {
        return route(
            implode(array_filter([
                'board',
                'post',
                $route,
            ]), '.'),
            [
                'board'   => $this->board_uri,
                'post_id' => $this->board_id,
            ] + $params,
            $abs
        );
    }

    /**
     * Determines if the post is made from the client's remote address.
     *
     * @return bool
     */
    public function isAuthoredByClient()
    {
        if (is_null($this->author_ip)) {
            return false;
        }

        return new IP($this->author_ip) === new IP();
    }

    /**
     * Determines if this is a bumpless post.
     *
     * @return bool
     */
    public function isBumpless()
    {
        if ($this->email == 'sage') {
            return true;
        }

        return false;
    }

    /**
     * Determines if this thread cannot be bumped.
     *
     * @return bool
     */
    public function isBumplocked()
    {
        return !is_null($this->bumplocked_at);
    }

    /**
     * Determines if this is cyclic.
     *
     * @return bool
     */
    public function isCyclic()
    {
        return false;
    }

    /**
     * Determines if this is deleted.
     *
     * @return bool
     */
    public function isDeleted()
    {
        return !is_null($this->deleted_at);
    }

    /**
     * Determines if this is the first reply in a thread.
     *
     * @return bool
     */
    public function isOp()
    {
        return is_null($this->reply_to);
    }

    /**
     * Determines if this thread is locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        return !is_null($this->locked_at);
    }

    /**
     * Determines if this thread is stickied.
     *
     * @return bool
     */
    public function isStickied()
    {
        return !is_null($this->stickied_at);
    }

    /**
     * Returns the author IP in a human-readable format.
     *
     * @return string
     */
    public function getAuthorIpAsString()
    {
        if ($this->hasAuthorIp()) {
            return$this->author_ip->toText();
        }

        return false;
    }

    /**
     * Returns author_ip as an instance of the support class.
     *
     * @return \App\Support\IP|null
     */
    public function getAuthorIpAttribute()
    {
        if (!isset($this->attributes['author_ip'])) {
            return;
        }

        if ($this->attributes['author_ip'] instanceof IP) {
            return $this->attributes['author_ip'];
        }

        $this->attributes['author_ip'] = new IP($this->attributes['author_ip']);

        return $this->attributes['author_ip'];
    }

    /**
     * Returns the bit size of the IP.
     *
     * @return int (32 or 128)
     */
    public function getAuthorIpBitSize()
    {
        if ($this->hasAuthorIp()) {
            return strpos($this->getAuthorIpAsString(), ':') === false ? 32 : 128;
        }

        return false;
    }

    /**
     * Returns a user-friendly list of ranges available for this IP.
     *
     * @return array
     */
    public function getAuthorIpRangeOptions()
    {
        $bitsize = $this->getAuthorIpBitSize();
        $range = range(0, $bitsize);
        $masks = [];

        foreach ($range as $mask) {
            $affectedIps = number_format(pow(2, $bitsize - $mask), 0);
            $masks[$mask] = trans_choice("board.ban.ip_range_{$bitsize}", $mask, [
                'mask' => $mask,
                'ips' => $affectedIps,
            ]);
        }

        return $masks;
    }

    /**
     * Returns the board model for this post.
     *
     * @return \App\Board
     */
    public function getBoard()
    {
        return $this->board()
            ->get()
            ->first();
    }

    /**
     * Returns a human-readable capcode string.
     *
     * @return string
     */
    public function getCapcodeName()
    {
        if ($this->capcode_capcode) {
            return trans_choice((string) $this->capcode_capcode, 0);
        } elseif ($this->capcode_id) {
            return $this->capcode->getCapcodeName();
        }

        return '';
    }

    /**
     * Parses the post text for citations.
     *
     * @return Collection
     */
    public function getCitesFromText()
    {
        return ContentFormatter::getCites($this);
    }

    /**
     * Returns a SHA1 checksum for this post's text.
     *
     * @param  bool Option. If return should be binary. Defaults false.
     *
     * @return string|binary
     */
    public function getChecksum($binary = false)
    {
        return $this->makeChecksum($this->body, $binary);
    }

    /**
     * Returns the last post made by this user across the entire site.
     *
     * @static
     *
     * @param string $ip
     *
     * @return \App\Post
     */
    public static function getLastPostForIP($ip = null)
    {
        if (is_null($ip)) {
            $ip = new IP();
        }

        return self::whereAuthorIP($ip)
            ->orderBy('created_at', 'desc')
            ->take(1)
            ->get()
            ->first();
    }

    /**
     * Returns the page on which this thread appears.
     * If the post is a reply, it will return the page it appears on in the thread, which is always 1.
     *
     * @return \App\Post
     */
    public function getPage()
    {
        if ($this->isOp()) {
            $board = $this->board()->with('settings')->get()->first();
            $visibleThreads = $board->threads()->op()->where('bumped_last', '>=', $this->bumped_last)->count();
            $threadsPerPage = (int) $board->getConfig('postsPerPage', 10);

            return floor(($visibleThreads - 1) / $threadsPerPage) + 1;
        }

        return 1;
    }

    /**
     * Returns the post model for the most recently featured post.
     *
     * @static
     *
     * @param int $dayRange Optional. Number of days at most that the last most featured post can be in. Defaults 3.
     *
     * @return \App\Post
     */
    public static function getPostFeatured($dayRange = 3)
    {
        $oldestPossible = \Carbon\Carbon::now()->subDays($dayRange);

        return static::where('featured_at', '>=', $oldestPossible)
            ->withEverything()
            ->orderBy('featured_at', 'desc')
            ->first();
    }

    /**
     * Returns the post model using the board's URI and the post's local board ID.
     *
     * @static
     *
     * @param string $board_uri
     * @param int    $board_id
     *
     * @return \App\Post
     */
    public static function getPostForBoard($board_uri, $board_id)
    {
        return static::where([
                'board_uri' => $board_uri,
                'board_id' => $board_id,
            ])
            ->first();
    }

    /**
     * Returns the model for this post's original post (what it is a reply to).
     *
     * @return \App\Post
     */
    public function getOp()
    {
        return $this->op()
            ->get()
            ->first();
    }

    /**
     * Returns a few posts for the front page.
     *
     * @static
     *
     * @param int  $number  How many to pull.
     * @param bool $sfwOnly If we only want SFW boards.
     *
     * @return \Illuminate\Database\Eloquent\Collection of Post
     */
    public static function getRecentPosts($number = 16, $sfwOnly = true)
    {
        return static::where('body_has_content', true)
            ->whereHas('board', function ($query) use ($sfwOnly) {
                $query->where('is_indexed', '=', true);
                $query->where('is_overboard', '=', true);

                if ($sfwOnly) {
                    $query->where('is_worksafe', '=', true);
                }
            })
            ->with('board')
            ->with(['board.assets' => function ($query) {
                $query->whereBoardIcon();
            }])
            ->limit($number)
            ->orderBy('post_id', 'desc')
            ->get();
    }

    /**
     * Returns the latest reply to a post.
     *
     * @return Post|null
     */
    public function getReplyLast()
    {
        return $this->replies()
            ->orderBy('post_id', 'desc')
            ->take(1)
            ->get()
            ->first();
    }

    /**
     * Returns all replies to a post.
     *
     * @return \Illuminate\Database\Eloquent\Collection of Post
     */
    public function getReplies()
    {
        if (isset($this->replies)) {
            return $this->replies;
        }

        return $this->replies()
            ->withEverything()
            ->orderBy('post_id', 'asc')
            ->get();
    }

    /**
     * Returns the last few replies to a thread for index views.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRepliesForIndex()
    {
        return $this->replies()
            ->forIndex()
            ->get()
            ->reverse();
    }

    /**
     * Returns a set of posts for an update request.
     *
     * @static
     *
     * @param Carbon $sinceTime
     * @param Board  $board
     * @param Post   $thread
     * @param bool   $includeHTML If the posts should also have very large 'content_html' values.
     *
     * @return Collection of Posts
     */
    public static function getUpdates($sinceTime, Board $board, Post $thread, $includeHTML = false)
    {
        $posts = static::whereInUpdate($sinceTime, $board, $thread)->get();

        if ($includeHTML) {
            foreach ($posts as $post) {
                $post->setAppendHTML(true);
            }
        }

        return $posts;
    }

    /**
     * Returns if this post has an attached IP address.
     *
     * @return bool
     */
    public function hasAuthorIp()
    {
        return $this->author_ip !== null;
    }

    /**
     * Determines if this post has a body message.
     *
     * @return bool
     */
    public function hasBody()
    {
        $body = false;
        $body_html = false;

        if (isset($this->attributes['body'])) {
            $body = strlen(trim((string) $this->attributes['body'])) > 0;
        }

        if (isset($this->attributes['body_html'])) {
            $body_html = strlen(trim((string) $this->attributes['body_html'])) > 0;
        }

        return $body || $body_html;
    }

    /**
     * Get the appends attribute.
     * Not normally available to models, but required for API responses.
     *
     * @param array $appends
     *
     * @return array
     */
    public function getAppends()
    {
        return $this->appends;
    }

    /**
     * Pull threads for the overboard.
     *
     * @static
     *
     * @param int $page
     *
     * @return Collection of static
     */
    public static function getThreadsForOverboard($page = 0)
    {
        $postsPerPage = 10;

        $rememberTags = ['site.overboard.pages'];
        $rememberTimer = 30;
        $rememberKey = "site.overboard.page.{$page}";
        $rememberClosure = function () use ($page, $postsPerPage) {
            $boards  = [];
            $threads = static::whereHas('board', function ($query) {
                    $query->where('is_indexed', true);
                    $query->where('is_overboard', true);
                })
                ->op()
                ->withEverything()
                ->with(['replies' => function ($query) {
                    $query->forIndex();
                }])
                ->orderBy('bumped_last', 'desc')
                ->skip($postsPerPage * ($page - 1))
                ->take($postsPerPage)
                ->get();

            // The way that replies are fetched forIndex pulls them in reverse order.
            // Fix that.
            foreach ($threads as $thread) {
                if (!isset($boards[$thread->board_uri])) {
                    $boards[$thread->board_uri] = Board::getBoardWithEverything($thread->board_uri);
                }

                $thread->setRelation('board', $boards[$thread->board_uri]);
                $replyTake = $thread->stickied_at ? 1 : 5;

                $thread->body_parsed = $thread->getBodyFormatted();
                $thread->replies = $thread->replies
                    ->sortBy('post_id')
                    ->splice(-$replyTake, $replyTake);

                $thread->replies->each(function($reply) use ($boards) {
                    $reply->setRelation('board', $boards[$reply->board_uri]);
                });

                $thread->prepareForCache();
            }

            return $threads;
        };

        switch (env('CACHE_DRIVER')) {
            case 'file':
            case 'database':
                $threads = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
                break;

            default:
                $threads = Cache::tags($rememberTags)->remember($rememberKey, $rememberTimer, $rememberClosure);
                break;
        }

        return $threads;
    }

    /**
     * Prepares a thread and its relationships for a complete cache.
     *
     * @return \App\Post
     */
    public function prepareForCache($board = null)
    {
        //# TODO ##
        // Find a better way to do this.
        // Call these methods so we typecast the IP as an IP class before
        // we invoke memory caching.
        $this->author_ip;
        $board = $this->getRelation('board' ?: $this->load('board'));

        foreach ($this->replies as $reply) {
            $reply->author_ip;
            $reply->setRelation('board', $board);
        }

        return $this;
    }

    /**
     * Sets the value of $this->appends to the input.
     * Not normally available to models, but required for API responses.
     *
     * @param array $appends
     *
     * @return array
     */
    public function setAppends(array $appends)
    {
        return $this->appends = $appends;
    }

    /**
     * Quickly add html to the append list for this model.
     *
     * @param bool $add defaults true
     *
     * @return Post
     */
    public function setAppendHTML($add = true)
    {
        $appends = $this->getAppends();

        if ($add) {
            $appends[] = 'html';
        } elseif (($key = array_search('html', $appends)) !== false) {
            unset($appends[$key]);
        }

        $this->setAppends($appends);

        return $this;
    }

    /**
     * Stores author_ip as an instance of the support class.
     *
     * @param \App\Support\IP|string|null $value The IP to store.
     *
     * @return \App\Support\IP|null
     */
    public function setAuthorIpAttribute($value)
    {
        if (!is_null($value) && !is_binary($value)) {
            $value = new IP($value);
        }

        return $this->attributes['author_ip'] = $value;
    }

    /**
     * Sets the bumplock property timestamp.
     *
     * @param bool $bumplock
     *
     * @return \App\Post
     */
    public function setBumplock($bumplock = true)
    {
        if ($bumplock) {
            $this->bumplocked_at = $this->freshTimestamp();
        } else {
            $this->bumplocked_at = null;
        }

        return $this;
    }

    /**
     * Sets the deleted timestamp.
     *
     * @param bool $delete
     *
     * @return \App\Post
     */
    public function setDeleted($delete = true)
    {
        if ($delete) {
            $this->deleted_at = $this->freshTimestamp();
        } else {
            $this->deleted_at = null;
        }

        return $this;
    }

    /**
     * Sets the locked property timestamp.
     *
     * @param bool $lock
     *
     * @return \App\Post
     */
    public function setLocked($lock = true)
    {
        if ($lock) {
            $this->locked_at = $this->freshTimestamp();
        } else {
            $this->locked_at = null;
        }

        return $this;
    }

    /**
     * Sets the sticky property of a post and updates relevant timestamps.
     *
     * @param bool $sticky
     *
     * @return \App\Post
     */
    public function setSticky($sticky = true)
    {
        if ($sticky) {
            $this->stickied = true;
            $this->stickied_at = $this->freshTimestamp();
        } else {
            $this->stickied = false;
            $this->stickied_at = null;
        }

        return $this;
    }

    public function scopeAndAttachments($query)
    {
        return $query->with('attachments');
    }

    public function scopeAndBacklinks($query)
    {
        return $query->with([
            'backlinks' => function ($query) {
                $query->has('post');
                $query->orderBy('post_id', 'asc');
            },
            'backlinks.post' => function ($query) {
                $query->select('post_id', 'board_uri', 'board_id', 'reply_to', 'reply_to_board_id');
            },
        ]);
    }

    public function scopeAndBoard($query)
    {
        return $query->with('board');
    }

    public function scopeAndBans($query)
    {
        return $query->with(['bans' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);
    }

    public function scopeAndCapcode($query)
    {
        return $query
            ->leftJoin('roles', function ($join) {
                $join->on('roles.role_id', '=', 'posts.capcode_id');
            })
            ->addSelect(
                'roles.capcode as capcode_capcode',
                'roles.role as capcode_role',
                'roles.name as capcode_name'
            );
    }

    public function scopeAndCites($query)
    {
        return $query->with('cites', 'cites.cite');
    }

    public function scopeAndEditor($query)
    {
        return $query
            ->leftJoin('users', function ($join) {
                $join->on('users.user_id', '=', 'posts.updated_by');
            })
            ->addSelect(
                'users.username as updated_by_username'
            );
    }

    public function scopeAndFlag($query)
    {
        return $query->with('flag');
    }

    public function scopeAndFirstAttachment($query)
    {
        return $query->with(['attachments' => function ($query) {
            $query->limit(1);
        }]);
    }

    public function scopeAndReplies($query)
    {
        return $query->with(['replies' => function ($query) {
            $query->withEverything();
        }]);
    }

    public function scopeAndPromotedReports($query)
    {
        return $query->with(['reports' => function ($query) {
            $query->whereOpen();
            $query->wherePromoted();
        }]);
    }

    public function scopeWhereAuthorIP($query, $ip)
    {
        $ip = new IP($ip);

        return $query->where('author_ip', $ip->toSQL());
    }

    public function scopeWhereBump($query)
    {
        return $query->whereNot('email', "sage");
    }

    public function scopeWhereBumpless($query)
    {
        return $query->where('email', "sage");
    }

    public function scopeIpString($query, $ip)
    {
        return $query->whereAuthorIP($ip);
    }

    public function scopeIpBinary($query, $ip)
    {
        return $query->whereAuthorIP($ip);
    }

    public function scopeOp($query)
    {
        return $query->where('reply_to', null);
    }

    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', static::freshTimestamp()->subDay());
    }

    public function scopeForIndex($query)
    {
        return $query->withEverythingForReplies()
            ->orderBy('post_id', 'desc')
            ->takePerGroup('reply_to', 5);
    }

    public function scopeReplyTo($query, $replies = false)
    {
        if ($replies instanceof \Illuminate\Database\Eloquent\Collection) {
            $thread_ids = [];

            foreach ($replies as $thread) {
                $thread_ids[] = (int) $thread->post_id;
            }

            return $query->whereIn('reply_to', $thread_ids);
        } elseif (is_numeric($replies)) {
            return $query->where('reply_to', '=', $replies);
        } else {
            return $query->where('reply_to', 'not', null);
        }
    }

    public function scopeWithEverything($query)
    {
        return $query
            ->withEverythingForReplies()
            ->andBoard();
    }

    public function scopeWithEverythingAndReplies($query)
    {
        return $query
            ->withEverything()
            ->with(['replies' => function ($query) {
                $query->withEverythingForReplies();
                $query->orderBy('board_id', 'asc');
            }]);
    }

    public function scopeWithEverythingForReplies($query)
    {
        return $query
            ->addSelect('posts.*')
            ->andAttachments()
            ->andBans()
            ->andBacklinks()
            ->andCapcode()
            ->andCites()
            ->andEditor()
            ->andFlag()
            ->andPromotedReports();
    }

    public function scopeWhereHasReports($query)
    {
        return $query->whereHas('reports', function ($query) {
            $query->whereOpen();
        });
    }

    public function scopeWhereHasReportsFor($query, PermissionUser $user)
    {
        return $query->whereHas('reports', function ($query) use ($user) {
            $query->whereOpen();
            $query->whereResponsibleFor($user);
        })
            ->with(['reports' => function ($query) use ($user) {
                $query->whereOpen();
                $query->whereResponsibleFor($user);
            }]);
    }

    public function scopeWhereInThread($query, Post $thread)
    {
        if ($thread->attributes['reply_to_board_id']) {
            return $query->where(function ($query) use ($thread) {
                $query->where('board_id', $thread->attributes['reply_to_board_id']);
                $query->orWhere('reply_to_board_id', $thread->attributes['reply_to_board_id']);
            });
        } else {
            return $query->where(function ($query) use ($thread) {
                $query->where('board_id', $thread->attributes['board_id']);
                $query->orWhere('reply_to_board_id', $thread->attributes['board_id']);
            });
        }
    }

    /**
     * Logic for pulling posts for API updates.
     *
     * @param DbQuery $query     Provided by Laravel.
     * @param Board   $board
     * @param Carbon  $sinceTime
     * @param Post    $thread    Board ID.
     *
     * @return $query
     */
    public function scopeWhereInUpdate($query, $sinceTime, Board $board, Post $thread)
    {
        // Find posts in this board.
        return $query->where('posts.board_uri', $board->board_uri)
            // Include deleted posts.
            ->withTrashed()
            // Only pull posts in this thread, or that is this thread.
            ->where(function ($query) use ($thread) {
                $query->where('posts.reply_to_board_id', $thread->board_id);
                $query->orWhere('posts.board_id', $thread->board_id);
            })
            // Nab posts that've been updated since our sinceTime.
            ->where(function ($query) use ($sinceTime) {
                $query->where('posts.updated_at', '>', $sinceTime);
                $query->orWhere('posts.deleted_at', '>', $sinceTime);
            })
            // Fetch accessory tables too.
            ->withEverything()
            // Order by board id in reverse order (so they appear in the thread right).
            ->orderBy('posts.board_id', 'asc');
    }

    /**
     *Renders a single post.
     *
     * @return string HTML
     */
    public function toHTML($catalog, $multiboard, $preview)
    {
        $rememberTags = [
            "board.{$this->board->board_uri}",
            "post_{$this->post_id}",
            'post_html',
        ];
        $rememberTimer = 30;
        $rememberKey = "board.{$this->board->board_uri}.post_html.{$this->board_id}";
        $rememberClosure = function () use ($catalog, $multiboard, $preview) {
            $this->setRelation('attachments', $this->attachments);

            return \View::make('content.board.post', [
                // Models
                'board' => $this->board,
                'post' => $this,
                'user' => user(),

                // Statuses
                'catalog' => $catalog,
                'reply_to' => $this->reply_to ?: false,
                'multiboard' => $multiboard,
                'preview' => $preview,
            ])->render();
        };

        if (!user()->isAnonymous()) {
            return $rememberClosure();
        }

        if ($catalog) {
            $rememberTags[] = 'catalog_post';
            $rememberTimer += 30;
        }

        if ($multiboard) {
            $rememberTags[] = 'multiboard_post';
            $rememberTimer -= 20;
        }

        if ($preview) {
            $rememberTags[] = 'preview_post';
            $rememberTimer -= 20;
        }
        switch (env('CACHE_DRIVER')) {
            case 'file':
            case 'database':
                break;

            default:
                return Cache::tags($rememberTags)
                    ->remember($rememberKey, $rememberTimer, $rememberClosure);
        }

        return $rememberClosure();
    }

    /**
     * Sends a redirect to the post's page.
     *
     * @param string $action
     *
     * @return Response
     */
    public function redirect($action = null)
    {
        return redirect($this->getUrl($action));
    }

    /**
     * Pushes the post to the specified board, as a new thread or as a reply.
     * This autoatically handles concurrency issues. Creating a new reply without
     * using this method is forbidden by the `creating` event in ::boot.
     *
     *
     * @param App\Board &$board
     * @param App\Post  &$thread
     */
    public function submitTo(Board &$board, &$thread = null)
    {
        $this->board_uri = $board->board_uri;
        $this->author_ip = new IP();
        $this->author_country = $board->getConfig('postsAuthorCountry', false) ? new Geolocation() : null;
        $this->reply_last = $this->freshTimestamp();
        $this->bumped_last = $this->reply_last;
        $this->setCreatedAt($this->reply_last);
        $this->setUpdatedAt($this->reply_last);

        if (!is_null($thread) && !($thread instanceof self)) {
            $thread = $board->getLocalThread($thread);
        }

        if (user()->isAccountable()) {
            if (Cache::has('posting_now_'.$this->author_ip->toLong())) {
                return abort(429);
            }

            // Cache what time we're submitting our post for flood checks.
            Cache::put('posting_now_'.$this->author_ip->toLong(), true, 1);
            Cache::put('last_post_for_'.$this->author_ip->toLong(), $this->created_at->timestamp, 60);

            if ($thread instanceof self) {
                $this->reply_to = $thread->post_id;
                $this->reply_to_board_id = $thread->board_id;

                Cache::put('last_thread_for_'.$this->author_ip->toLong(), $this->created_at->timestamp, 60);
            }
        } else {
            $this->author_ip = null;

            if ($thread instanceof self) {
                $this->reply_to = $thread->post_id;
                $this->reply_to_board_id = $thread->board_id;
            }
        }

        // Handle tripcode, if any.
        if (preg_match('/^([^#]+)?(##|#)(.+)$/', $this->author, $match)) {
            // Remove password from name.
            $this->author = $match[1];
            // Whether a secure tripcode was requested, currently unused.
            $secure_tripcode_requested = ($match[2] == '##');
            // Convert password to tripcode, store tripcode hash in DB.
            $this->insecure_tripcode = ContentFormatter::formatInsecureTripcode($match[3]);
        }

        // Ensure we're using a valid flag.
        if (!$this->flag_id || !$board->hasFlag($this->flag_id)) {
            $this->flag_id = null;
        }

        // Store the post in the database.
        DB::transaction(function () use ($board, $thread) {
            // The objective of this transaction is to prevent concurrency issues in the database
            // on the unique joint index [`board_uri`,`board_id`] which is generated procedurally
            // alongside the primary autoincrement column `post_id`.

            // First instruction is to add +1 to posts_total and set the last_post_at on the Board table.
            DB::table('boards')
                ->where('board_uri', $this->board_uri)
                ->increment('posts_total', 1, [
                    'last_post_at' => $this->reply_last,
                ]);

            // Second, we record this value and lock the table.
            $boards = DB::table('boards')
                ->where('board_uri', $this->board_uri)
                ->lockForUpdate()
                ->select('posts_total')
                ->get();

            $posts_total = $boards[0]->posts_total;

            // Third, we store a unique checksum for this post for duplicate tracking.
            $board->checksums()->create([
                'checksum' => $this->getChecksum(),
            ]);

            // Optionally, we also expend the adventure.
            $adventure = BoardAdventure::getAdventure($board);

            if ($adventure) {
                $this->adventure_id = $adventure->adventure_id;
                $adventure->expended_at = $this->created_at;
                $adventure->save();
            }

            // We set our board_id and save the post.
            $this->board_id = $posts_total;
            $this->author_id = $this->makeAuthorId();
            $this->password = $this->makePassword($this->password);
            $this->save();

            // Optionally, the OP of this thread needs a +1 to reply count.
            if ($thread instanceof static) {
                // We're not using the Model for this because it fails under high volume.
                $threadNewValues = [
                    'updated_at' => $thread->updated_at,
                    'reply_last' => $this->created_at,
                    'reply_count' => $thread->replies()->count(),
                    'reply_file_count' => $thread->replyFiles()->count(),
                ];

                if (!$this->isBumpless() && !$thread->isBumplocked()) {
                    $threadNewValues['bumped_last'] = $this->created_at;
                }

                DB::table('posts')
                    ->where('post_id', $thread->post_id)
                    ->update($threadNewValues);
            }

            // Queries and locks are handled automatically after this closure ends.
        });

        // Process uploads.
        $uploads = [];

        // Check file uploads.
        if (is_array($files = Input::file('files'))) {
            $uploads = array_filter($files);

            if (count($uploads) > 0) {
                foreach ($uploads as $uploadIndex => $upload) {
                    if (file_exists($upload->getPathname())) {
                        FileStorage::createAttachmentFromUpload($upload, $this);
                    }
                }
            }
        } elseif (is_array($files = Input::get('files'))) {
            $uniques = [];
            $hashes = $files['hash'];
            $names = $files['name'];
            $spoilers = isset($files['spoiler']) ? $files['spoiler'] : [];

            $storages = FileStorage::whereIn('hash', $hashes)->get();

            foreach ($hashes as $index => $hash) {
                if (!isset($uniques[$hash])) {
                    $uniques[$hash] = true;
                    $storage = $storages->where('hash', $hash)->first();

                    if ($storage && !$storage->banned) {
                        $spoiler = isset($spoilers[$index]) ? $spoilers[$index] == 1 : false;

                        $upload = $storage->createAttachmentWithThis($this, $names[$index], $spoiler, false);
                        $upload->position = $index;
                        $uploads[] = $upload;
                    }
                }
            }

            $this->attachmentLinks()->saveMany($uploads);
            FileStorage::whereIn('hash', $hashes)->increment('upload_count');
        }


        // Finally fire event on OP, if it exists.
        if ($thread instanceof self) {
            $thread->setRelation('board', $board);
            Event::fire(new ThreadNewReply($thread));
        }

        if (user()->isAccountable()) {
            Cache::forget('posting_now_'.$this->author_ip->toLong());
        }

        return $this;
    }

    /**
     * Returns a thread with its replies for a thread view and leverages cache.
     *
     * @static
     *
     * @param string $board_uri Board primary key.
     * @param int    $board_id  Local board id.
     * @param string $uri       Optional. URI string for splicing the thread. Defaults to null, for no splicing.
     *
     * @return static
     */
    public static function getForThreadView($board_uri, $board_id, $uri = null)
    {
        // Prepare the board so that we do not have to make redundant searches.
        $board = null;

        if ($board_uri instanceof Board) {
            $board = $board_uri;
            $board_uri = $board->board_uri;
        } else {
            $board = Board::find($board_uri);
        }

        $rememberTags = ["board.{$board_uri}", 'threads'];
        $rememberTimer = 30;
        $rememberKey = "board.{$board_uri}.thread.{$board_id}";
        $rememberClosure = function () use ($board, $board_uri, $board_id) {
            $thread = static::where([
                'posts.board_uri' => $board_uri,
                'posts.board_id' => $board_id,
            ])->withEverythingAndReplies()->first();

            if ($thread) {
                $thread->setRelation('attachments', $thread->attachments);
                $thread->prepareForCache();
            }

            return $thread;
        };

        switch (env('CACHE_DRIVER')) {
            case 'file':
            case 'database':
                $thread = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
                break;

            default:
                $thread = Cache::tags($rememberTags)->remember($rememberKey, $rememberTimer, $rememberClosure);
                break;
        }

        if (!is_null($uri)) {
            return $thread->getReplySplice($uri);
        }

        return $thread;
    }
}
