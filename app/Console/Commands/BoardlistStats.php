<?php

namespace App\Console\Commands;

use App\Board;
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
class BoardlistStats extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'autocache:boardlist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild and cache the boardlist page.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Board::getBoardsForBoardlist(0, null, true);
    }
}
