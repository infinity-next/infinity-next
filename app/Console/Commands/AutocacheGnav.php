<?php

namespace App\Console\Commands;

use App\Services\NavigationService;
use Illuminate\Console\Command;
use Cache;
use Storage;

/**
 * Rebuilds navigation caches.
 *
 * @category   Command
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since  0.6.0
 */
class AutocacheGnav extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'autocache:gnav';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild and cache the global navigation.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $nav = app()->make(NavigationService::class);
        Cache::put('site.nav.primary', $nav->renderPrimaryNav(), now()->addHour());

    }
}
