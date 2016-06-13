<?php

namespace App\Console\Commands;

use App\Support\Geolocation;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;

class TorCheck extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'tor:check {ip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check record for Tor exit node.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (Geolocation::isTorExitNode($this->argument('ip'))) {
            $this->line("IP is an accessible Tor exit node.");
        } else {
            $this->line("IP is not recognized as a Tor exit node.");
        }
    }
}
