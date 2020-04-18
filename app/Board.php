<?php

namespace App;

use App\Contracts\Auth\Permittable;
use App\Services\ContentFormatter;
use App\Support\IP;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Acetone;
use DB;
use Cache;
use Settings;
use Session;
use Event;
use App\Events\BoardWasCreated;
use App\Events\BoardWasReassigned;

/**
 * Model representing boards.
 *
 * @category   Model
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class Board extends Model
{
    /**
     * The RegEx used to check the validity of a board uri.
     *
     * @var string
     */
    const URI_PATTERN = "^[a-z0-9]{1,32}\b$";
    const URI_PATTERN_INNER = '[a-z0-9]{1,32}';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'boards';

    /**
     * The primary key that is used by ::get().
     *
     * @var string
     */
    protected $primaryKey = 'board_uri';
    public $keyType = "string";

    /**
     * Denotes our primary key is not an autoincrementing integer.
     *
     * @var string
     */
    public $incrementing = false;

    /**
     * Denotes this instance is the currently "opened" board.
     *
     * @var bool
     */
    public $applicationSingleton = false;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'stats_plh',
        'stats_pph',
        'stats_ppd',
        'stats_active_users',
        'stats_active_ranges',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'board_uri',
        'title',
        'description',
        'created_at',
        'created_by',
        'operated_by',
        'posts_total',
        'is_indexed',
        'is_overboard',
        'is_worksafe',
        'last_post_at',
        'featured_at',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'created_by',
        'operated_by',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'board_uri' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_post_at' => 'datetime',
        'featured_at' => 'datetime',
    ];

    /**
     * A cache of compiled board settings.
     *
     * @var array which contains Options with BoardSetting.option_value pivot keys
     */
    protected $compiledSettings;

    public function assets()
    {
        return $this->hasMany(BoardAsset::class, 'board_uri');
    }

    public function checksums()
    {
        return $this->hasMany(PostChecksum::class, 'board_uri');
    }

    public function bans()
    {
        return $this->hasMany(Ban::class, 'board_uri');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'board_uri');
    }

    public function postAttachments()
    {
        return $this->hasManyThrough(PostAttachment::class, Post::class, 'board_uri', 'post_id');
    }

    public function logs()
    {
        return $this->hasMany(Log::class, 'board_uri');
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operated_by', 'user_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function threads()
    {
        return $this->hasMany(Post::class, 'board_uri')->whereNull('reply_to');
    }

    public function roles()
    {
        return $this->hasMany(Role::class, 'board_uri');
    }

    public function settings()
    {
        return $this->hasMany(BoardSetting::class, 'board_uri');
    }

    public function staffAssignments()
    {
        return $this->hasManyThrough(UserRole::class, Role::class, 'board_uri', 'role_id');
    }

    public function stats()
    {
        return $this->hasMany(Stats::class, 'board_uri');
    }

    public function tags()
    {
        return $this->belongsToMany(BoardTag::class, 'board_tag_assignments', 'board_uri', 'board_tag_id');
    }

    public function uniques()
    {
        return $this->hasManyThrough(StatsUnique::class, Stats::class, 'board_uri', 'stats_id');
    }

    public function clearCachedModel()
    {
        return Cache::forget("board.{$this->board_uri}");
    }

    public function clearCachedThreads()
    {
        switch (env('CACHE_DRIVER')) {
            case 'file':
                break;

            case 'database':
                DB::table('cache')
                    ->where('key', 'like', "%board.{$this->board_uri}.thread.%")
                    ->delete();
                break;

            default:
                Cache::tags("board.{$this->board_uri}", 'threads')->flush();
                break;
        }
    }

    public function clearCachedPages()
    {
        Cache::forget("board.{$this->board_uri}.catalog");
        Cache::forget("board.{$this->board_uri}.pages");
        Cache::tags("board.{$this->board_uri}.pages")->flush();
    }

    /**
     * @static
     *
     * @param \Carbon|Carbon $carbon A timestmap within the 0-60 minute block that is to be snapshotted.
     *
     * @return array of \App\Stats
     */
    public static function createStatsSnapshots(\Carbon\Carbon $carbon = null)
    {
        $stats = collect([]);

        if (is_null($carbon)) {
            $carbon = \Carbon\Carbon::now()->subHour()->minute(0)->second(0);
        }

        static::chunk(25, function ($boards) use ($stats, $carbon) {
            foreach ($boards as $board) {
                $stats = $stats->merge($board->createStatsSnapshot($carbon));
            }
        });

        return $stats;
    }

    /**
     * Generates a snapshot in the database for the previous hour.
     *
     * @param \Carbon\Carbon $carbon A timestamp within the 0-60 minute block that is to be snapshotted.
     *
     * @return array of new \App\Stats
     */
    public function createStatsSnapshot(\Carbon\Carbon $carbon)
    {
        $carbonStart = $carbon->minute(0)->second(0);
        $carbonEnd = (clone $carbonStart);
        $carbonEnd = $carbonEnd->addHour()->minute(0)->second(0)->subSecond();

        $posts = $this->posts()
            ->withTrashed()
            ->where('created_at', '>=', $carbonStart)
            ->where('created_at', '<=', $carbonEnd)
            ->select('post_id', 'author_ip', 'reply_to')
            ->get();

        if ($posts->count() === 0) {
            return collect([]);
        }

        // Unique IPs.
        $authorsUnique = [];
        // Unique Post IDs.
        $postsUnique = [];
        // Unique \16 ranges.
        $rangesUnique = [];
        // Unique Thread Post IDs.
        $threadsUnique = [];

        foreach ($posts as $post) {
            $postsUnique[$post->post_id] = true;

            if (is_null($post->reply_to)) {
                $threadsUnique[$post->post_id] = false;
            }

            if (!is_null($post->author_ip)) {
                $ip = new IP($post->author_ip);

                if (!isset($authorsUnique[$ip->toText()])) {
                    $authorsUnique[$ip->toText()] = $ip->toLong();

                    $range = new IP("{$ip->getStart()}/16");
                    $rangesUnique[$range->getStart()] = $range->toLong();
                }
            }
        }


        // Save uniques
        $statsRows = [];
        $uniques = [
            'authors' => array_values($authorsUnique),
            'posts' => array_keys($postsUnique),
            'ranges' => array_values($rangesUnique),
            'threads' => array_keys($threadsUnique),
        ];

        foreach ($uniques as $statsKey => $uniqueValues) {
            $statsBits = [];

            foreach ($uniqueValues as $uniqueValue) {
                $statsBits[] = [
                    'unique' => (int) $uniqueValue,
                ];
            }

            $statsRow = $this->stats()->updateOrCreate([
                'stats_time' => $carbonStart,
                'stats_type' => $statsKey,
            ], [
                'counter' => count($statsBits),
            ]);

            if (!$statsRow->exists) {
                $statsRow->save();
            }

            $statsRow->uniques()->createMany($statsBits);

            $statsRows[] = $statsRow;
        }


        return collect($statsRows);
    }

    /**
     * Fixes post totals on all boards.
     *
     * @static
     */
    public static function fixPostsTotal()
    {
        static::chunk(50, function ($boards) {
            foreach ($boards as $board) {
                $last_post = Post::where('board_uri', $board->board_uri)->select('board_id')->withTrashed()->orderBy('board_id', 'desc')->first();
                $last_board_id = $last_post ? (int) $last_post->board_id : 0;
                $posts_total = (int) $board->posts_total;

                if (max((int) $posts_total, (int) $last_board_id) > $posts_total) {
                    echo "\n{$board->board_uri} -- {$posts_total} < {$last_board_id}!!!!!!!!!\n\n";
                } else {
                    echo $board->board_uri." -- {$posts_total} >= {$last_board_id}\n";
                }

                $board->posts_total = max((int) $posts_total, (int) $last_board_id);
                $board->save();
            }
        });
    }

    /**
     * Gets the default album art for an audio file.
     *
     * @return string url
     */
    public function getAudioArtUrl()
    {
        return asset('static/img/assets/audio.gif');
    }

    /**
     * Returns either the URL for an asset or their default item.
     *
     * @param  string  asset name
     *
     * @return string url
     */
    public function getAssetUrl($asset)
    {
        $assetObj = $this->assets
            ->where('asset_type', $asset)
            ->first();

        if ($assetObj) {
            return $assetObj->getUrl();
        }

        switch ($asset) {
            case 'board_icon':
                return media_url('static/img/assets/Favicon_'.($this->isWorksafe() ? 'Burichan' : 'Yotsuba').'.ico', false);
            case 'file_spoiler':
                return media_url('static/img/assets/spoiler.png', false);
            case 'file_deleted':
                return media_url('static/img/assets/deleted.png', false);
        }

        return media_url('static/img/errors/404.jpg', false);
    }

    /**
     * Returns all board_banned type BoardAsset items.
     *
     * @return Collection
     */
    public function getBannedImages()
    {
        return $this->assets
            ->where('asset_type', 'board_banned');
    }

    /**
     * Returns a single board_banner BoardAsset.
     *
     * @return BoardAsset
     */
    public function getBannerRandom()
    {
        $banners = $this->getBanners();

        if (count($banners) > 0) {
            return $banners->random();
        }

        return false;
    }

    /**
     * Returns a URL for a single banner, and will consider defaults.
     *
     * @return string
     */
    public function getBannerUrl()
    {
        $banners = $this->getBanners();

        if (count($banners) > 0) {
            return $banners->random()->getUrl();
        } elseif (!user()->isAccountable()) {
            return asset('static/img/logo_tor.png');
        } elseif (!$this->isWorksafe()) {
            return asset('static/img/logo_yotsuba.png');
        } else {
            return asset('static/img/logo.png');
        }

        return false;
    }

    /**
     * Returns all board_banner type BoardAsset items.
     *
     * @return Collection
     */
    public function getBanners()
    {
        return $this->assets
            ->where('asset_type', 'board_banner');
    }

    /**
     * Returns assignable castes.
     *
     * @return collection
     */
    public function getCastes()
    {
        return $this->roles()
            ->whereLevel(Role::ID_JANITOR)
            ->get();
    }

    /**
     * This very important method determines the compiled configuration for a board.
     * It does this by taking extant board Options and then laying on top Board Settings.
     * Anything null defaults, anything with a value takes that place.
     *
     * @param string $option_name If null, returns compiled config.
     * @param mixed  $fallback    If not null, returns itself if there is nothing defined.
     *
     * @return mixed
     */
    public function getConfig($option_name = null, $fallback = null)
    {
        $config = &$this->compiledSettings;

        if (!is_array($config)) {
            $config = [];
            // Available options + defaults
            $options = Option::where('option_type', 'board')->get();
            // Defined settings
            $settings = $this->settings;

            foreach ($options as $option) {
                $option->option_value = $option->default_value;
                $config[$option->option_name] = $option;

                foreach ($settings as $setting) {
                    if ($setting->option_name === $option->option_name) {
                        $setting->data_type = $option->data_type;
                        $option->option_value = $setting->option_value;
                        break;
                    }
                }
            }
        }

        if (!is_null($option_name)) {
            foreach ($config as $setting) {
                if ($setting->attributes['option_name'] == $option_name) {
                    $value = $setting->getDisplayValue();

                    if (is_null($value) || $value == '') {
                        return $fallback;
                    }

                    return $value;
                }
            }
        } else {
            return $config;
        }

        return $fallback;
    }

    /**
     * Returns the board's display name.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->title;
    }

    /**
     * Returns the most recent featured boards.
     *
     * @return array
     */
    public static function getFeatured($count = 5)
    {
        return static::whereNotNull('featured_at')
            ->with(['assets' => function ($query) {
                $query->whereBoardIcon();
            }])
            ->orderBy('featured_at', 'desc')
            ->limit(6)
            ->get();
    }

    /**
     * Returns flag board assets.
     *
     * @return Collection Of \App\BoardAsset
     */
    public function getFlags()
    {
        return $this->assets
            ->where('asset_type', 'board_flags')
            ->sortBy('asset_name');
    }

    /**
     * Returns the board's fully qualified icon image url.
     *
     * @return string
     */
    public function getIconUrl()
    {
        $icon = $this->assets->where('asset_type', 'board_icon')->first();

        if (!$icon) {
            if ($this->is_worksafe) {
                return asset('/static/img/assets/Favicon_Burichan.ico');
            } else {
                return asset('/static/img/assets/Favicon_Yotsuba.ico');
            }
        }

        return $icon->getUrl();
    }

    /**
     * Returns the board's primary language. Defaults to the site language.
     *
     * @return string
     */
    public function getLanguageAttribute()
    {
        return $this->getConfig('boardLanguage', config('app.locale', 'en'));
    }

    public function getLocalReply($local_id)
    {
        return $this->posts()
            ->where('board_id', $local_id)
            ->get()
            ->first();
    }

    public function getLogs()
    {
        return $this->logs()
            ->with('user')
            ->take(100)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getPageCount()
    {
        return Cache::remember("board.{$this->board_uri}.pages", now()->addHour(), function () {
            $visibleThreads = $this->threads()->count();
            $threadsPerPage = (int) $this->getConfig('postsPerPage', now()->addMinutes(10));
            $pageCount = ceil($visibleThreads / $threadsPerPage);

            return $pageCount > 0 ? $pageCount : 1;
        });
    }

    public function getOwnerRole()
    {
        return $this->roles()
            ->where('role', 'owner')
            ->where('caste', null)
            ->first();
    }

    /**
     * Returns an array of castes currently assigned to ths board under the specified role.
     *
     * @param string   $role     Role group.
     * @param int|null $ignoreID Optional ID to exclude from results.
     *
     * @return array
     */
    public function getRoleCastes($role, $ignoreID = null)
    {
        return $this->roles()->where(function ($query) use ($role, $ignoreID) {
            $query->where('role', $role);

            if (!is_null($ignoreID)) {
                $query->where('role_id', '!=', $ignoreID);
            }
        });
    }

    public function getSidebarContent()
    {
        $ContentFormatter = new ContentFormatter();

        return $ContentFormatter->formatSidebar($this->getConfig('boardSidebarText'));
    }

    public function getSpoilerUrl()
    {
        return $this->getAssetUrl('file_spoiler');
    }

    public function getStaff()
    {
        $staff = [];
        $roles = Role::with('users')
            ->where('board_uri', $this->board_uri)
            ->get();

        $roles = [];
        foreach ($this->roles as $role) {
            foreach ($role->users as $user) {
                $staff[$user->user_id] = $user;

                if (!isset($roles[$user->user_id])) {
                    $roles[$user->user_id] = [];
                }

                $roles[$user->user_id][] = $role;
            }
        }

        foreach ($roles as $user_id => $role) {
            $staff[$user_id]->setRelation('roles', collect($role));
        }

        return $staff;
    }

    /**
     * Returns a 'stats_active_users' attribute for JSON output.
     *
     * @return int
     */
    public function getStatsActiveUsersAttribute()
    {
        $stats = $this->stats->where('stats_type', 'authors');

        if ($stats->count()) {
            $uniques = new Collection();

            foreach ($stats as $stat) {
                $uniques = $uniques->merge($stat->uniques);
            }

            // The word unique is not unique in this line of code ...
            // We're finding the number of stat row "bits" and then
            // sub-selecting the distinct values from that.

            // So if /b/ has 4 stat rows with these values as uniques
            // 192.168.0.1, 192.168.0.2, 192.168.0.3
            // 192.168.0.1, 192.168.0.2
            // 192.168.0.2, 192.168.0.3
            // 192.168.0.1
            // The actual derived value is 3.
            return $uniques->unique('unique')->count();
        }

        return 0;
    }

    /**
     * Returns a 'stats_active_ranges' attribute for JSON output.
     *
     * @return int
     */
    public function getStatsActiveRangesAttribute()
    {
        $stats = $this->stats->where('stats_type', 'ranges');

        if ($stats->count()) {
            $uniques = new Collection();

            foreach ($stats as $stat) {
                $uniques = $uniques->merge($stat->uniques);
            }

            return $uniques->unique('unique')->count();
        }

        return 0;
    }

    /**
     * Returns a 'stats_plh' attribute for JSON output.
     *
     * @return int
     */
    public function getStatsPlhAttribute()
    {
        $oneHourAgo = Carbon::now()->minute(0)->second(0)->subHour();

        $stats = $this->stats
            ->where('stats_type', 'posts')
            ->filter(function ($item) use ($oneHourAgo) {
                return $item->stats_time->gte($oneHourAgo);
            });

        if ($stats->count()) {
            return (int) $stats->sum('counter');
        }

        return 0;
    }

    /**
     * Returns a 'stats_pph' attribute for JSON output.
     *
     * @return float
     */
    public function getStatsPphAttribute()
    {
        $sevenDaysAgo = Carbon::now()->minute(0)->second(0)->subDays(7);

        $stats = $this->stats
            ->where('stats_type', 'posts')
            ->filter(function ($item) use ($sevenDaysAgo) {
                return $item->stats_time->gte($sevenDaysAgo);
            });

        if ($stats->count()) {
            return number_format($stats->sum('counter') / 168, 2);
        }

        return 0;
    }

    /**
     * Returns a 'stats_ppd' attribute for JSON output.
     *
     * @return float
     */
    public function getStatsPpdAttribute()
    {
        $sevenDaysAgo = Carbon::now()->minute(0)->second(0)->subDays(7);

        $stats = $this->stats
            ->where('stats_type', 'posts')
            ->filter(function ($item) use ($sevenDaysAgo) {
                return $item->stats_time->gte($sevenDaysAgo);
            });

        if ($stats->count()) {
            return number_format($stats->sum('counter') / 24, 2);
        }

        return 0;
    }

    /**
     * Returns a fully qualified URL for a route on this board.
     *
     * @param string $route Optional route addendum.
     * @param array $params Optional array of parameters to be added.
     * @param bool $abs Options indicator if the URL is to be absolute.
     *
     * @return string
     */
    public function getUrl($route = "catalog", array $params = [], $abs = true)
    {
        // Most people like URLs to be /foo/ instead of /foo.
        if ($route === "index" && empty($params)) {
            $trailing_slash = "/";
        } else {
            $trailing_slash = "";
        }

        return route(
            implode(array_filter(['board', $route]), '.'),
            [ 'board' => $this, ] + $params,
            $abs
        ).$trailing_slash;
    }

    /**
     * Returns a fully qualified URL for a route on this board in the panel.
     *
     * @param string $route Optional route addendum.
     * @param array $params Optional array of parameters to be added.
     * @param bool $abs Options indicator if the URL is to be absolute.
     *
     * @return string
     */
    public function getPanelUrl($route = "basic", array $params = [], $abs = true)
    {
        return route(
            implode(array_filter(['panel.board', $route]), '.'),
            [ 'board' => $this, ] + $params,
            $abs
        );
    }

    /**
     * Returns a find->replace array for board wordfilters.
     *
     * @return array
     */
    public function getWordfilters()
    {
        $filters = $this->getConfig('boardWordFilter', []);
        $find = [];
        $replace = [];

        if (isset($filters['find'])) {
            $find = (array) $filters['find'];
        }

        if (isset($filters['replace'])) {
            $replace = (array) $filters['replace'];
        }

        $filters = array_combine($find, $replace);

        foreach ($filters as $fFind => $fReplace) {
            if ($fFind === '') {
                unset($filters[$fFind]);
            }
        }

        return $filters;
    }

    public function hasBannedUri()
    {
        $bannedUris = (string) Settings::get('boardUriBanned');
        $bannedUris = explode(PHP_EOL, $bannedUris);

        foreach ($bannedUris as $bannedUri) {
            $bannedUri = trim(str_replace(["\r\n", "\n", "\r"], ' ', $bannedUri));

            if (preg_match("/{$bannedUri}/im", $this->board_uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns if this board has a specific flag asset currently loaded.
     *
     * @param int $asset_id
     *
     * @return bool
     */
    public function hasFlag($asset_id)
    {
        if (!isset($this->assets)) {
            return false;
        }

        return (bool) $this->assets->where('asset_type', 'board_flags')->where('board_asset_id', (int) $asset_id)->count();
    }

    /**
     * Returns if this board has flag assets currently loaded.
     *
     * @return bool
     */
    public function hasFlags()
    {
        if (!isset($this->assets)) {
            return false;
        }

        return (bool) $this->assets->where('asset_type', 'board_flags')->count();
    }

    public function hasStylesheet()
    {
        if (!$this->isWorksafe()) {
            return true;
        }

        $optionEnable = $this->getConfig('boardCustomCSSEnable', false);
        $optionSteal = $this->getConfig('boardCustomCSSSteal', false);
        $optionText = strlen((string) $this->getConfig('boardCustomCSS', '')) > 0;

        return $optionEnable && ($optionSteal || $optionText);
    }

    /**
     * Returns the current stylesheet's raw CSS.
     *
     * @return string
     */
    public function getStylesheet()
    {
        return Cache::remember("board.{$this->board_uri}.stylesheet", now()->addMinutes(30), function () {
            $stealFrom = $this->getConfig('boardCustomCSSSteal', '');

            if ($stealFrom != '') {
                $stealFrom = Board::with('settings', 'settings.option')
                    ->where('board_uri', $stealFrom)
                    ->first();

                if ($stealFrom && $stealFrom->exists) {
                    $stealFromStyle = $stealFrom->getConfig('boardCustomCSSEnable', false) ? $stealFrom->getConfig('boardCustomCSS') : '';

                    if ($stealFromStyle != '') {
                        return "/**\n * This style is borrowed from /{$stealFrom->board_uri}/.\n */\n\n\n".$stealFromStyle;
                    }
                }
            }

            $style = $this->getConfig('boardCustomCSS', '');

            if ($style == '' && !$this->isWorksafe()) {
                $style = file_get_contents(public_path().'/static/css/skins/next-yotsuba.css');
            }

            return $style;
        });
    }

    /**
     * Returns a fully qualified URL for the board's stylesheet.
     *
     * @return string
     */
    public function getStylesheetUrl()
    {
        return $this->getUrl('style', [
            'style' => "style_{$this->updated_at->timestamp}",
        ]);
    }

    /**
     * Returns a single board with all data, using the cache if possible.
     *
     * @static
     *
     * @param string $board_uri
     *
     * @return \App\Board
     */
    public static function getBoardWithEverything($board_uri)
    {
        $rememberTimer = now()->addHour();
        $rememberKey = "board.{$board_uri}";
        $rememberClosure = function () use ($board_uri) {
            $board = static::find($board_uri);

            if ($board instanceof Board && $board->exists) {
                $board->load([
                    'assets',
                    'assets.storage',
                    'settings',
                ]);

                $board->getConfig();

                return $board;
            }

            return;
        };

        $board = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);

        return $board;
    }

    /**
     * Returns the entire board list and all associative boards by levying the cache.
     *
     * @static
     *
     * @param int  $start  Optional. Splice start position. Defaults 0.
     * @param int  $length Optional. Splice length. Defaults null (return all).
     * @param bool $force  Optional. Forces a recache. Defaults false.
     *
     * @return array
     */
    public static function getBoardsForBoardlist($start = 0, $length = null, $force = false)
    {
        // Compiling a large board list requires a lot of resources.
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 60);

        // This timer is very precisely set to be a minute after the turn of the next hour.
        // Laravel's CRON system will add new stat rows and we will be free to recache
        // with the up-to-date information.
        $rememberTimer = Carbon::now()->minute(1)->second(0)->addHour();
        $rememberKey = 'site.boardlist';
        $rememberClosure = function () use ($rememberKey) {
            $boards = static::select('board_uri', 'title', 'description', 'posts_total', 'last_post_at', 'is_indexed', 'is_worksafe')
                ->with([
                    'tags',
                    'settings' => function ($query) {
                        $query->whereIn('option_name', [
                            'boardLanguage',
                        ]);
                    },
                    'stats' => function ($query) {
                        $query->where('stats_time', '>=', Carbon::now()->minute(0)->second(0)->subDays(7));
                    },
                    'stats.uniques',
                ])
                ->get()
                ->sort(function ($a, $b) {
                    // Sort by active users, then last post time.
                    return $b->stats_active_ranges - $a->stats_active_ranges
                        ?: $b->stats_active_users - $a->stats_active_users
                        ?: ($b->last_post_at ? $b->last_post_at->timestamp : 0) - ($a->last_post_at ? $a->last_post_at->timestamp : 0);
                })
                ->toArray();

            foreach ($boards as &$board) {
                if (isset($board['settings'])) {
                    $newSettings = [];
                    foreach ($board['settings'] as &$setting) {
                        $newSettings[$setting['option_name']] = $setting['option_value'];
                        unset($setting);
                    }
                    $board['settings'] = $newSettings;
                }
            }

            return $boards;
        };

        if ($force) {
            $boards = $rememberClosure();

            Cache::forget($rememberKey);
            Cache::put($rememberKey, $boards, $rememberTimer);
        } else {
            $boards = Cache::remember($rememberKey, $rememberTimer, $rememberClosure);
        }

        if (is_null($length)) {
            $length = count($boards);
        }

        return array_splice($boards, $start, $length);
    }

    public function getThreadByBoardId($board_id)
    {
        if ($board_id instanceof Post) {
            throw new InvalidArgumentException('Board::getThreadByBoardId was called using a real Laravel post model. Only a board id should be passed.');
        } else {
            return Post::getForThreadView($this, $board_id);
        }

        return;
    }

    public function getThreads()
    {
        return $this->threads()
            ->with('attachments')
            ->get();
    }

    public function getThreadsForCatalog($page = 0)
    {
        $postsPerCatalog = 150;
        $postsPerPage = $this->getConfig('postsPerPage', 10);

        $rememberTags = ["board.{$this->board_uri}.pages"];
        $rememberTimer = now()->addMinutes(30);
        $rememberKey = "board.{$this->board_uri}.catalog";
        $rememberClosure = function () use ($page, $postsPerCatalog, $postsPerPage) {
            $threads = $this->threads()
                ->withEverythingForReplies()
                ->with(['replies' => function ($query) {
                    $query->where('body_has_content', true)->orderBy('post_id', 'desc')->limit(10);
                }])
                ->orderBy('stickied', 'desc')
                ->orderBy('bumped_last', 'desc')
                ->skip($postsPerCatalog * ($page - 1))
                ->take($postsPerCatalog)
                ->get();

            foreach ($threads as $threadIndex => $thread) {
                $thread->setRelation('board', $this);
                $thread->attachments->each(function($postAttachment) use ($thread) {
                    $postAttachment->setRelation('board', $this);
                    $postAttachment->setRelation('post', $thread);
                });
                $thread->page_number = floor($threadIndex / $postsPerPage) + 1;
                $thread->prepareForCache();
            }

            return $threads;
        };

        //$threads = Cache::tags($rememberTags)->remember($rememberKey, $rememberTimer, $rememberClosure);
        $threads = $rememberClosure();

        return $threads;
    }

    public function getThreadsForIndex($page = 0)
    {
        $postsPerPage = $this->getConfig('postsPerPage', 10);

        $rememberTags = ["board.{$this->board_uri}.pages"];
        $rememberTimer = 30;
        $rememberKey = "board.{$this->board_uri}.page.{$page}";
        $rememberClosure = function () use ($page, $postsPerPage) {
            $threads = $this->threads()
                ->withEverything()
                ->with(['replies' => function ($query) {
                    $query->forIndex();
                }])
                ->orderBy('stickied', 'desc')
                ->orderBy('bumped_last', 'desc')
                ->skip($postsPerPage * ($page - 1))
                ->take($postsPerPage)
                ->get();

            // The way that replies are fetched forIndex pulls them in reverse order.
            // Fix that.
            foreach ($threads as $thread) {
                $thread->setRelation('board', $this);
                $replyTake = $thread->stickied_at ? 1 : 5;

                $thread->body_parsed = $thread->getBodyFormatted();
                $thread->replies = $thread->replies
                    ->sortBy('post_id')
                    ->splice(-$replyTake, $replyTake);

                foreach ($thread->replies as $reply) {
                    $reply->setRelation('board', $this);
                    $reply->attachments->each(function($postAttachment) use ($thread) {
                        $postAttachment->setRelation('board', $this);
                        $postAttachment->setRelation('post', $thread);
                    });
                }

                $thread->prepareForCache();
            }

            return $threads;
        };

        //$threads = Cache::tags($rememberTags)->remember($rememberKey, $rememberTimer, $rememberClosure);
        $threads = $rememberClosure();

        return $threads;
    }

    /**
     * Determines if this citation is permitted to appear visibly over a post.
     *
     * @param \App\PostCite $backlink The backlink to be checked.
     *
     * @return bool If the backlink may appear.
     */
    public function isBacklinkAllowed(PostCite $backlink)
    {
        // Always allow internal links.
        if ($backlink->post_board_uri == $this->board_uri) {
            return true;
        }

        // Always forbid blacklist items.
        $blacklist = explode("\n", (string) $this->getConfig('boardBacklinksBlacklist'));

        if (in_array($backlink->post_board_uri, $blacklist)) {
            return false;
        }

        // Are we forcing whitelisting?
        if (!$this->getConfig('boardBacklinksCrossboard')) {
            // Yes, we are forcing whitelisting.
            // Check and see if this board uri is whitelisted.
            $whitelist = explode("\n", (string) $this->getConfig('boardBacklinksWhitelist'));

            if (!in_array($backlink->post_board_uri, $whitelist)) {
                return false;
            }
        }

        return true;
    }

    public function isWorksafe()
    {
        return (bool) $this->is_worksafe;
    }

    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = empty($value) ? null : $value;
    }

    public function scopeAndAssets($query)
    {
        return $query->with('assets', 'assets.storage');
    }

    public function scopeAndCreator($query)
    {
        return $query
            ->join('users as creator', function ($join) {
                $join->on('creator.user_id', '=', 'boards.created_by');
            })
            ->addSelect(
                'boards.*',
                'creator.username as created_by_username'
            );
    }

    public function scopeAndOperator($query)
    {
        return $query
            ->join('users as operator', function ($join) {
                $join->on('operator.user_id', '=', 'boards.operated_by');
            })
            ->addSelect(
                'boards.*',
                'operator.username as operated_by_username'
            );
    }

    public function scopeAndStaff($query)
    {
        return $query->with('staffAssignments.user');
    }

    public function scopeAndStaffAssignments($query)
    {
        return $query->with('staffAssignments');
    }

    public function scopeWhereIndexed($query, $indexed = true)
    {
        return $query->where('is_indexed', (bool) $indexed);
    }

    public function scopeWhereLastPost($query, $hours = 48)
    {
        return $query->where('last_post_at', '>=', $this->freshTimestamp()->subHours($hours));
    }

    public function scopeWhereNSFW($query)
    {
        return $query->where('is_worksafe', false);
    }

    public function scopeWhereOverboard($query)
    {
        return $query->where('is_overboard', true);
    }

    public function scopeWherePublic($query)
    {
        return $query->whereIndexed()->whereOverboard();
    }

    public function scopeWhereSFW($query)
    {
        return $query->where('is_worksafe', true);
    }

    public function scopeWhereHasTags($query, $tags)
    {
        if (!is_array($tags)) {
            $tags = explode(',', $tags);
        }

        return $query->whereHas('tags', function ($query) use ($tags) {
            $query->whereIn('tag', $tags);
        });
    }
}
