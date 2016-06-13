<?php

namespace App\Console\Commands;

use Cache;
use Illuminate\Console\Command;
use Illuminate\Foundation\Inspiring;
use Storage;

class TorPull extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'tor:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull Tor exit node records.';

    /**
     * The URL we use for looksups.
     *
     * @static
     * @var string
     */
    const LOOKUP_URL = "https://check.torproject.org/cgi-bin/TorBulkExitList.py";

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Query string parameters
        $query = [
            'ip' => getHostByName(php_uname('n')),
        ];

        $ips = collect();
        $ips = $ips->merge($this->getPortIps($query, 80));
        $ips = $ips->merge($this->getPortIps($query, 443));
        $ips = $ips->merge($this->getPortIps($query, 8080));
        $ips = $ips->merge($this->getPortIps($query, 8443));
        $ips = $ips->unique();

        Storage::put('TorExitNodes.ser', $ips);
        Cache::forever('tor-exit-nodes', $ips);
        $this->info("Tor Pull: Recorded the existence of {$ips->count()} Tor exit nodes.");
    }

    /**
     * Gets remote API data for Tor exit nodes.
     *
     * @param array $query
     * @param int $port
     *
     * @return \Illuminate\Support\Collection
     */
    public function getPortIps($query, $port = 80)
    {
        // Pull API response.
        $response = file_get_contents(
            static::LOOKUP_URL
            .'?'
            .http_build_query(
                $query + [
                    'port' => $port,
                ]
            )
        );

        $lines = explode("\n", $response);

        // Remove comment rows.
        return array_filter($lines, function($item)
        {
            return strpos($item, '#') === false && strlen($item) > 0;
        });
    }
}
