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
     * Loads all settings.
     *
     * @return collection
     */
    public function fetchSettings()
    {
        if (!is_null(user())) {
            return Cache::remember('site.settings', now()->addMinutes(30), function () {
                return SiteSetting::getAll();
            });
        }

        return collect();
    }
}
