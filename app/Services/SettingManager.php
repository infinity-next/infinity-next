<?php

namespace App\Services;

use App\Board;
use App\SiteSetting;
use App\Option;
use Cache;
use DB;
use Exception;

class SettingManager
{
    /**
     * Public settings.
     * THESE WILL BE EXPOSED TO THE FRONT-END WITH EVERY REQUEST.
     *
     * @var array of SiteSetting names
     */
    protected static $whitelist = [
        'attachmentFilesize',
        'postFloodTime',
        'threadFloodTime',
    ];

    /**
     * Cached settings for the entire site.
     *
     * @var collection of SiteSetting
     */
    protected $settings;

    /**
     * Remembers if we have a stable database connection.
     *
     * @var bool
     */
    protected $db;

    /**
     * Create a new authentication controller instance.
     */
    public function __construct($app)
    {
        $this->settings = $this->fetchSettings();
    }

    /**
     * Magic method allows invocation of class as shortcut to getSetting.
     *
     * @param string $option_name
     *
     * @return mixed
     */
    public function __invoke($option_name)
    {
        return $this->getSetting($option_name);
    }

    /**
     * Returns the value of a single setting.
     *
     * @param string $option_name
     * @param mixed  $fallback    Option. Defaults to null.
     *
     * @return mixed
     */
    public function get($option_name, $fallback = null)
    {
        $setting = $this->getSetting($option_name);

        if (is_null($setting)) {
            return $fallback;
        }

        return $setting;
    }

    /**
     * Returns all settings in an array.
     *
     * @return array
     */
    public function getArray()
    {
        return $this->getSettings()->toArray();
    }

    /**
     * Returns all settings in a json array string for front-end injection.
     *
     * @return string (json array)
     */
    public function getJson()
    {
        $settings = [];

        foreach (static::$whitelist as $setting) {
            $settings[$setting] = $this->get($setting);
        }

        return json_encode($settings);
    }

    /**
     * Returns the primary navigation array.
     *
     * @return array of [key => url]
     */
    public function getNavigationPrimary()
    {
        $nav = [
            'home' => route('site.home'),
            'boards' => route('site.boardlist'),
            'recent_posts' => route('site.overboard.catalog.all'),
            'panel' => route('panel.home'),
        ];


        if ($this->hasDB()) {
            $manager = app()->make(UserManager::class);

            if ($manager->user && $manager->user->canCreateBoard()) {
                $nav['new_board'] = route('panel.boards.create');
            }
        }

        if ($this->get('adventureEnabled')) {
            $nav['adventure'] = route('panel.adventure');
        }


        return $nav;
    }

    /**
     * Returns the primary navigation board list.
     *
     * @return array|false Returns false if the setting boardListShow is disabled.
     */
    public function getNavigationPrimaryBoards()
    {
        if ($this->hasDB() && $this->get('boardListShow', false)) {
            if (Cache::has('site.boardlist')) {
                return Cache::remember('site.gnav.boards', 1, function () {
                    $popularBoards = collect();
                    $recentBoards = collect();

                    $popularBoardArray = Board::getBoardsForBoardlist(0, 20);

                    foreach ($popularBoardArray as $popularBoard) {
                        $popularBoards->push(new Board($popularBoard));
                    }

                    $recentBoards = Board::where('posts_total', '>', 0)
                        ->whereNotNull('last_post_at')
                        ->wherePublic()
                        ->whereNotIn('board_uri', $popularBoards->pluck('board_uri'))
                        ->select('board_uri', 'title')
                        ->orderBy('last_post_at', 'desc')
                        ->take(20)
                        ->get();

                    return [
                        'popular_boards' => $popularBoards,
                        'recent_boards' => $recentBoards,
                    ];
                });
            }

            return [
                'popular_boards' => collect(),
                'recent_boards' => collect(),
            ];
        }

        return false;
    }

    /**
     * Returns the value of a single setting.
     *
     * @param string $option_name
     *
     * @return mixed
     */
    public function getSetting($option_name)
    {
        foreach ($this->settings as $settings) {
            if ($settings->option_name == $option_name) {
                return $settings->option_value;
            }
        }

        return;
    }

    /**
     * Returns all settings.
     *
     * @return collection
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * Determines if we have a DB connection.
     *
     * @return bool
     */
    public function hasDB()
    {
        if (!isset($this->db)) {
            try {
                $this->db = (bool) DB::connection()->getDatabaseName();
            } catch (Exception $e) {
                $this->db = false;
            }
        }

        return $this->db;
    }

    /**
     * Loads all settings.
     *
     * @return collection
     */
    public function fetchSettings()
    {
        if ($this->hasDB()) {
            return Cache::remember('site.settings', 30, function () {
                return SiteSetting::getAll();
            });
        }

        return collect();
    }
}
