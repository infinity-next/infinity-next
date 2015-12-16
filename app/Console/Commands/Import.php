<?php namespace App\Console\Commands;

use App\Board;
use App\BoardTag;
use App\FileAttachment;
use App\Post;
use App\Role;
use App\RolePermission;
use App\User;
use App\UserRole;

use App\Support\IP;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Inspiring;
use Symfony\Component\Console\Helper\ProgressBar;

use DB;
use Config;
use Schema;

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
	 * Target database connection.
	 */
	protected $tcon;
	
	/**
	 * Our database connection.
	 *
	 * @var DB
	 */
	protected $hcon;
	
	
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->importSchema   = $this->argument('schema');
		$this->targetDatabase = $this->argument('database');
		
		$this->targetHost     = $this->option('host')     ?: env('DB_HOST');
		$this->targetUser     = $this->option('username') ?: env('DB_USERNAME');
		$this->targetPass     = $this->option('password') ?: env('DB_PASSWORD');
		$this->targetSystem   = $this->option('system')   ?: env('DB_SYSTEM');
		
		$this->targetLocation = $this->option('location') ?: null;
		
		// if (!$this->confirm("Import {$this->targetSystem} database `{$this->targetDatabase}` on {$this->targetHost} as '{$this->targetUser}'@<PASS:".($this->targetPass?"YES":"NO").">"))
		// {
		// 	$this->info("Aborted.");
		// 	exit;
		// }
		
		$this->info("Attempting import connection." . PHP_EOL);
		
		if ($this->createDatabaseConnection())
		{
			$this->comment("We're good!");
			
			$this->importPrepare();
			$this->importInfinity();
			$this->importCleanup();
			
			$this->info("Import has completed.");
			$this->comment("( ≖‿≖)");
		}
		else
		{
			$this->error("Could not establish database connection.");
		}
	}
	
	public function createDatabaseConnection()
	{
		$this->hcon = DB::connection();
		
		$connection = [
			'driver'    => $this->targetSystem,
			'host'      => $this->targetHost,
			'database'  => $this->targetDatabase,
			'username'  => $this->targetUser,
			'password'  => $this->targetPass,
			'charset'   => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix'    => '',
			'strict'    => false,
		];
		
		// Set our connection details.
		Config::set('database.connections._import', $connection);
		
		// Create the connection.
		$this->tcon = DB::connection('_import');
		return !!$this->tcon;
	}
	
	public function getImportState()
	{
		$file = storage_path("infinity.import");
		
		try
		{
			$state = file_get_contents($file);
		}
		catch (\Exception $e)
		{
			$state = null;
		}
		
		return $state;
	}
	
	public function importPrepare()
	{
		if ($this->getImportState() != "")
		{
			$this->line("\tSkipping database prep.");
			return false;
		}
		
		$this->line("\tPrepping database.");
		
		# OUR TABLES
		$attachmentsTable    = $this->hcon->table( with(new FileAttachment)->getTable() );
		$postTable           = $this->hcon->table( with(new Post)->getTable() );
		$boardTable          = $this->hcon->table( with(new Board)->getTable() );
		$roleTable           = $this->hcon->table( with(new Role)->getTable() );
		$rolePermissionTable = $this->hcon->table( with(new RolePermission)->getTable() );
		$userTable           = $this->hcon->table( with(new User)->getTable() );
		$userRoleTable       = $this->hcon->table( with(new UserRole)->getTable() );
		
		# DESTROY OUR EXISTING INFORMATION
		$attachmentsTable->delete();
		$postTable->delete();
		$boardTable->delete();
		$userRoleTable->delete();
		$userTable->delete();
		$rolePermissionTable->delete();
		$roleTable->delete();
		
		# Yes, we are required to drop and recreate these FKs to get ID reassignment working.
		# This is for PostgreSQL.
		try
		{
			Schema::table('posts', function(Blueprint $table)
			{
				$table->dropForeign('posts_capcode_id_foreign');
			});
			Schema::table('role_permissions', function(Blueprint $table)
			{
				$table->dropForeign('role_permissions_role_id_foreign');
			});
			Schema::table('user_roles', function(Blueprint $table)
			{
				$table->dropForeign('user_roles_role_id_foreign');
			});
		}
		catch (\Exception $e)
		{
			$this->comment("\tSkipping FK drops. Probably already missing.");
		}
		
		if (DB::connection() instanceof \Illuminate\Database\PostgresConnection)
		{
			DB::statement("DROP SEQUENCE IF EXISTS roles_role_id_seq CASCADE;");
		}
	}
	
	public function importCleanup()
	{
		$this->comment("\tCleaning up.");
		
		try
		{
			if (DB::connection() instanceof \Illuminate\Database\PostgresConnection)
			{
				DB::statement("CREATE SEQUENCE roles_role_id_seq OWNED BY \"roles\".\"role_id\";");
				DB::statement("SELECT setval('roles_role_id_seq', COALESCE((SELECT MAX(role_id)+1 FROM roles), 1), false);");
			}
		}
		catch (\Exception $e)
		{
			$this->info("\t\tPostgreSQL did not need role sequencing.");
		}
		
		Schema::table('posts', function(Blueprint $table)
		{
			$table->foreign('capcode_id')
				->references('role_id')->on('roles')
				->onDelete('set null')->onUpdate('cascade');
		});
		Schema::table('role_permissions', function(Blueprint $table)
		{
			$table->foreign('role_id')
				->references('role_id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
		});
		Schema::table('user_roles', function(Blueprint $table)
		{
			$table->foreign('role_id')
				->references('role_id')->on('roles')
				->onDelete('cascade')->onUpdate('cascade');
		});
	}
	
	/**
	 * Imports the Infinity database.
	 *
	 * @return void
	 */
	public function importInfinity()
	{
		$file = storage_path("infinity.import");
		$state = $this->getImportState();
		
		switch ($state)
		{
			case null :
			case "" :
			case "boards" :
			case "users" :
				file_put_contents($file, "boards");
				$this->importInfinityRolesAndBoards();
			
			case "tags" :
				file_put_contents($file, "tags");
				$this->importInfinityBoardTags();
			
			case "posts" :
				file_put_contents($file, "posts");
				$this->importInfinityPosts();
			
			break;
			default :
				$this->error("Import state \"{$state}\" invalid.");
		}
		
	}
	
	/**
	 * Imports users and creates roles based on addition data in that row.
	 *
	 * @return void
	 */
	public function importInfinityRolesAndBoards()
	{
		# BORROW A SEEDER
		require base_path() . "/database/seeds/RoleSeeder.php";
		
		$RoleSeeder = new \RoleSeeder;
		$RoleSeeder->setCommand($this);
		$RoleSeeder->runMaster();
		
		# REPAIR SEQUENCE
		if (DB::connection() instanceof \Illuminate\Database\PostgresConnection)
		{
			$this->comment("\tCreating role_id sequence again.");
			DB::statement("CREATE SEQUENCE roles_role_id_seq;");
			$pgSeqNext = DB::table('roles')->select(DB::raw("(MAX(\"role_id\") + 1) AS next"))->pluck("next");
			DB::statement("ALTER SEQUENCE roles_role_id_seq OWNED BY \"roles\".\"role_id\" RESTART WITH {$pgSeqNext};");
			DB::statement("ALTER TABLE roles ALTER COLUMN role_id SET DEFAULT nextval('roles_role_id_seq');");
		}
		
		# THEIR TABLES
		$tBoardsTable = $this->tcon->table("boards")
			->join('board_create', 'boards.uri', '=', 'board_create.uri')
			->select('boards.*', 'board_create.time');
		$tModsTable   = $this->tcon->table("mods");
		
		
		# BEGIN USER IMPORT
		$this->info("\tImporting Users ...");
		
		$userAdmin = null;
		$userBoardRelationships = [];
		$usersImported = 0;
		
		$tModsTable->chunk(100, function($mods) use (&$userAdmin, &$userBoardRelationships, &$usersImported)
		{
			$this->line("\t\tHandling 100 users ...");
			
			foreach ($mods as $mod)
			{
				# CREATE USER
				$user = new User([
					'username' => $mod->username,
					'email'    => property_exists($mod, "email") ? $mod->email ?: null : null,
					
					'password' => null,
					'password_legacy' => json_encode([
						'hasher' => "Vichan",
						'hash'   => $mod->password,
						'salt'   => $mod->salt,
					]),
				]);
				
				// 8chan has an issue with duplicates.
				try
				{
					$saved = $user->save();
				}
				catch (\Exception $e)
				{
					$saved = false;
				}
				
				if ($saved)
				{
					++$usersImported;
					
					# REMEMBER ROLES
					if ($mod->boards)
					{
						// TODO
						// Pull these values from config when formal importer created.
						switch ($mod->type)
						{
							// Janitor (Disabled)
							case 10 :
							// Disabled
							case 99 :
								$this->comment("\t\tMod {$user->username} is disabled.");
								break;
							
							// Board volunteer
							case 19 :
							// Board owner
							case 20 :
								if (!isset($userBoardRelationships[$mod->boards]))
								{
									$userBoardRelationships[$mod->boards] = [];
								}
								
								$userBoardRelationships[$mod->boards][$user->user_id] = $mod->type;
								break;
							
							// Global volunteer
							case 25 :
								$this->comment("\t\tSetting {$user->username} to Global Mod.");
								$user->roles()->attach( Role::ID_MODERATOR );
								break;
							
							// Admin
							case 30 :
								$this->comment("\t\tSetting {$user->username} to Global Admin.");
								$user->roles()->attach( Role::ID_ADMIN );
								$userAdmin = $userAdmin ?: $user;
								break;
								
							// Not identified!
							default :
								$this->error("\t\tMod {$user->username} has invalid mod type {$mod->type}.");
								break;
						}
					}
				}
			}
		});
		
		$this->info("\tImported {$usersImported} users(s).");
		
		if (!$userAdmin)
		{
			$this->comment("\tFailed to import an admin. This may cause problems.");
		}
		
		unset($mods, $mod, $user);
		
		
		# BEGIN BOARD IMPORT
		$this->info("\tImporting Boards ...");
		
		$boardsImported = 0;
		
		$tBoardsTable->chunk(100, function($boards) use (&$boardsImported, $userAdmin, $userBoardRelationships)
		{
			$this->line("\t\tHandling 100 boards ...");
			
			foreach ($boards as $tBoard)
			{
				// We have an array like [board_uri => [ user_id => user_role ]] in the user import.
				// This fetches the first key of the biggest item.
				$boardOwner = [];
				if (isset($userBoardRelationships[$tBoard->uri]))
				{
					$boardOwner = array_keys($userBoardRelationships[$tBoard->uri], max($userBoardRelationships[$tBoard->uri]));
				}
				
				// Or it defaults to our admin.
				if (!isset($boardOwner[0]))
				{
					$this->comment("\t\t/{$tBoard->uri}/ has no known owner, assuming it is {$userAdmin->username}.");
					$boardOwner = $userAdmin->user_id;
				}
				else
				{
					$boardOwner = isset($boardOwner) ? $boardOwner[0] : $userAdmin->user_id;
				}
				
				
				$hBoard = new Board([
					'board_uri'    => $tBoard->uri,
					'title'        => $tBoard->title,
					'description'  => $tBoard->subtitle,
					
					'created_at'   => new Carbon($tBoard->time),
					
					'created_by'   => $boardOwner,
					'operated_by'  => $boardOwner,
					
					'posts_total'  => $tBoard->posts_total,
					
					'is_indexed'   => !!$tBoard->indexed,
					'is_overboard' => !!$tBoard->indexed,
					'is_worksafe'  => !!$tBoard->sfw,
				]);
				
				if ($hBoard->save())
				{
					++$boardsImported;
				}
				else
				{
					$this->error("Failed to save /{$hBoard->board_uri}/.");
				}
			}
		});
		
		$this->info("\tImported {$boardsImported} board(s).");
		
		unset($boards, $tBoard, $hBoard);
		
		
		# BEGIN ROLE CONFIGURATION
		$this->info("\tCreating roles ...");
		
		$roleBoardVols   = 0;
		$roleBoardOwners = 0;
		$roleBoardSkips  = 0;
		
		// Okay, so apparently sometimes there are users that own boards that don't exist.
		// We cannot crate roles for these boards, so lets prune useless data.
		$boardsWeCareAbout = Board::select('board_uri')->whereIn('board_uri', array_keys($userBoardRelationships))->get()->pluck('board_uri');
		
		if ($boardsWeCareAbout->count() != count($userBoardRelationships))
		{
			$this->comment("\t\tThere are " . (count($userBoardRelationships) - $boardsWeCareAbout->count()) . " board(s) which users own that do not exist!");
		}
		
		foreach ($boardsWeCareAbout as $board)
		{
			$ownerRole   = null;
			$janitorRole = null;
			
			$roles = $userBoardRelationships[$board];
			
			foreach ($roles as $mod => $role)
			{
				// TODO
				// Pull these values from config when formal importer created.
				switch ($role)
				{
					// Board volunteer
					case 19 :
						$janitorRole = $janitorRole ?: Role::getJanitorRoleForBoard($board);
						
						$userRole  = new UserRole([
							'user_id' => $mod,
							'role_id' => $janitorRole->role_id,
						]);
						
						if ($userRole->save())
						{
							++$roleBoardVols;
						}
						break;
					
					// Board owner
					case 20 :
						$ownerRole = $ownerRole ?: Role::getOwnerRoleForBoard($board);
						
						$userRole  = new UserRole([
							'user_id' => $mod,
							'role_id' => $ownerRole->role_id,
						]);
						
						if ($userRole->save())
						{
							++$roleBoardOwners;
						}
						break;
					
					default :
						$this->line("\t\tI don't know what to do with role {$role} for user id {$mod} in {$board}.");
						break;
				}
			}
		}
		
		$this->info("\t\tCreated {$roleBoardOwners} owner(s) and {$roleBoardVols} janitor(s) ");
		$this->info("\t\tPulled roles from " . count($userBoardRelationships) . " boards with relationships.");
		
		unset($board, $roles, $mod, $role, $userRole, $janitorRole, $ownerRole);
	}
	
	/**
	 * Imports board stats.
	 *
	 * @return void
	 */
	public function importInfinityPosts()
	{
		$this->info("\tImporting posts ...");
		
		Board::chunk(1, function($boards)
		{
			foreach ($boards as $board)
			{
				$this->line("\t\tImporting posts from /{$board->board_uri}/");
				$tTable = $this->tcon->table("posts_{$board->board_uri}");
				$postsMade = 0;
				$validThreads = [];
				
				// Threads first, replies by id.
				$board->posts()->forceDelete();
				$tTable->orderByRaw('thread asc, id asc')->chunk(50, function($posts) use (&$validThreads, &$board, &$postsMade)
				{
					$this->line("\t\t\tImporting 50 more posts ...");
					$models = [];
					
					// thread, subject, email, name, trip, capcode, body, body_nomarkup, time, bump, files, num_files, filehash, password, ip, sticky, locked, cycle, sage, embed, edited_at
					// post_id | board_uri | board_id | reply_to | reply_to_board_id | reply_count | reply_last | bumped_last | created_at | updated_at | updated_by | deleted_at | stickied | stickied_at | bumplocked_at | locked_at | author_ip | author_id | author_country | author_ip_nulled_at | author | insecure_tripcode | capcode_id | adventure_id | subject | email | password | body | body_parsed | body_parsed_at | body_html | featured_at | body_too_long | body_parsed_preview | flag_id | reply_file_count
					foreach ($posts as $post)
					{
						$model = new Post;
						$thread = null;
						
						if (!$post->thread)
						{
							$thread = null;
						}
						else if (isset($validThreads[$post->thread]))
						{
							$thread = $validThreads[$post->thread];
						}
						else
						{
							dd($validThreads);
							continue;
						}
						
						$model->board_uri = $board->board_uri;
						$model->board_id = $post->id;
						$model->reply_to = $thread ? $thread->post_id : null;
						$model->reply_to_board_id = $thread ? $thread->board_id : null;
						
						$model->created_at = Carbon::now()->setTimestamp($post->time);
						$model->updated_at = $model->edited_at ? Carbon::now()->setTimestamp($post->edited_at) : $model->created_at;
						$model->updated_by = null;
						$model->deleted_at = null;
						$model->bumped_last = $post->bump;
						
						$model->stickied_at = $post->thread || !$post->sticky ? null : $model->created_at;
						$model->locked_at = $post->thread || !$post->locked ? null : $model->created_at;
						
						$model->body = null;
						$model->body_parsed = null;
						$model->body_parsed_at = null;
						$model->body_html = $post->body;
						$model->body_too_long = null;
						$model->body_parsed_preview = null;
						
						$model->author = $post->name;
						$model->author_ip = $post->ip ? new IP($post->ip) : null;
						$model->email = $post->email;
						$model->insecure_tripcode = $post->trip ?: null;
						$model->password = $post->password ? $model->makePassword( $post->password ) : null;
						
						if ($model->save())
						{
							++$postsMade;
							
							if (!$post->thread)
							{
								$validThreads[$post->id] = $model;
							}
						}
						else
						{
							$this->warn("\t\t\t\t/{$board->board_uri}/{$post->id} failed to save.");
						}
					}
				});
				
				$this->line("\t\t\tMade {$postsMade} post(s) for /{$board->board_uri}/.");
			}
		});
	}
	
	/**
	 * Imports board tags.
	 *
	 * @return void
	 */
	public function importInfinityBoardTags()
	{
		# THEIR TABLES
		$tTagsTable = $this->tcon->table("board_tags");
		
		# BEGIN USER IMPORT
		$this->info("\tImporting Tags ...");
		
		
		$tTagsTable->chunk(100, function($tags)
		{
			$this->line("\t\tImporting 100 tags.");
			
			$boardTags = [];
			$tagModels = [];
			
			foreach ($tags as $tag)
			{
				if (!isset($boardTags[$tag->uri]))
				{
					$boardTags[$tag->uri] = [];
				}
				
				if (!isset($tagModels[$tag->tag]))
				{
					$tagModels[$tag->tag] = BoardTag::firstOrCreate([
						'tag' => $tag->tag,
					]);
				}
				
				$boardTags[$tag->uri] = $tagModels[$tag->tag];
			}
			
			foreach ($boardTags as $board_uri => $tags)
			{
				$board = Board::find($board_uri);
				
				if ($board)
				{
					$board->tags()->attach($tags);
				}
				else
				{
					$this->warn("\t\t\tBoard \"{$board_uri}\" could not be found to add tags to.");
				}
			}
			
			
			unset($tag, $tagModel, $tagModels, $boardTags);
		});
	}
}
