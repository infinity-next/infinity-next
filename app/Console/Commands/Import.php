<?php namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Foundation\Inspiring;
use Symfony\Component\Console\Helper\ProgressBar;

use DB;
use Config;

class Import extends Command {
	
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'import';
	
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature  = 'import
		{database        : Name of the database we are importing.}
		{schema=infinity : Our migration schema.}
		{--system=       : System (like MySQL) of the target database. Defaults DB_SYSTEM.}
		{--location=     : The full path to your installation directory we\'re importing from. If unspecified, does a try (assetless) import.}
		{--host=         : Host for the target database. Defaults DB_HOST.}
		{--username=     : Username for the target database. Defaults DB_USERNAME.}
		{--password=     : Password for the target database. Defaults DB_PASSWORD.}';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Import a foreign database.';
	
	
	/**
	 * Import schema.
	 *
	 * @var string
	 */
	protected $importSchema;
	
	/**
	 * Target database name.
	 *
	 * @var string
	 */
	protected $targetDatabase;
	
	/**
	 * Target database host.
	 *
	 * @var string
	 */
	protected $targetHost;
	
	/**
	 * Target database username.
	 *
	 * @var string
	 */
	protected $targetUser;
	
	/**
	 * Target database user password.
	 *
	 * @var string
	 */
	protected $targetPass;
	
	/**
	 * Target install location.
	 *
	 * @var string
	 */
	protected $targetLocation;
	
	/**
	 * Target system.
	 *
	 * @var string
	 */
	protected $targetSystem;
	
	
	/**
	 * Our database connection.
	 *
	 * @var DB
	 */
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->importSchema   = $this->argument('schema');
		$this->targetDatabase = $this->argument('database');
		
		$this->targetHost     = $this->option('host')     ?: DB_HOST;
		$this->targetUser     = $this->option('username') ?: env('DB_USERNAME');
		$this->targetPass     = $this->option('password') ?: env('DB_PASSWORD');
		$this->targetSystem   = $this->option('system')   ?: env('DB_SYSTEM');
		
		$this->targetLocation = $this->option('location') ?: null;
		
		// if (!$this->confirm("Import {$this->targetSystem} database `{$this->targetDatabase}` on {$this->targetHost} as '{$this->targetUser}'@<PASS:".($this->targetPass?"YES":"NO").">"))
		// {
		// 	$this->info("Aborted.");
		// 	exit;
		// }
		
		$this->comment("Attempting import conneciton." . PHP_EOL);
		
		$this->createDatabaseConnection();
	}
	
	public function createDatabaseConnection()
	{
		$connection = [
			'driver'    => $this->targetSystem,
			'host'      => $this->targetHost,
			'database'  => $this->targetDatabase,
			'username'  => $this->targetUser,
			'password'  => $this->targetPass,
			// 'charset'   => 'utf8',
			// 'collation' => 'utf8_unicode_ci',
			// 'prefix'    => '',
		];
		
		// Set our connection details.
		Config::set('database.connections._import', $connection);
		
		// Create the connection.
		$this->connection = DB::connection('_import');
		dd($this->connection);
		exit;
	}
}