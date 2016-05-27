<?php

namespace App\Console\Commands;

use App\Board;
use Illuminate\Console\Command;

class RecordStats extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'recordstats';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Record board statistics';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Generate an activity snapshot.
        Board::createStatsSnapshots();
        Board::getBoardsForBoardlist(0, null, true);
    }
}
