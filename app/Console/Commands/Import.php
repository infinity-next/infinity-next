<?php namespace App\Console\Commands {

use App\Board;
use App\BoardAsset;
use App\BoardTag;
use App\BoardSetting;
use App\FileStorage;
use App\FileAttachment;
use App\Page;
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
use Illuminate\Http\Request;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\HttpFoundation\File\File;

use DB;
use Config;
use File as FileFacade;
use Schema;
use Storage;

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
		
		$this->targetLocation = $this->option('location') ? rtrim($this->option('location'), '/') : null;
		
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
			$state = trim(file_get_contents($file));
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
		
		$this->info("\t\t\FKing `posts`.");
		try {
			Schema::table('posts', function(Blueprint $table)
			{
				$table->foreign('capcode_id')
					->references('role_id')->on('roles')
					->onDelete('set null')->onUpdate('cascade');
			});
		}
		catch (\Exception $e) {
			$this->line("\t\t\t`posts` did not need FKing.");
		}
		
		$this->info("\t\t\FKing `role_permissions`.");
		try {
			Schema::table('role_permissions', function(Blueprint $table)
			{
				$table->foreign('role_id')
					->references('role_id')->on('roles')
					->onDelete('cascade')->onUpdate('cascade');
			});
		}
		catch (\Exception $e) {
			$this->line("\t\t\t`role_permissions` did not need FKing.");
		}
		
		$this->info("\t\t\FKing `user_roles`.");
		try {
			Schema::table('user_roles', function(Blueprint $table)
			{
				$table->foreign('role_id')
					->references('role_id')->on('roles')
					->onDelete('cascade')->onUpdate('cascade');
			});
		}
		catch (\Exception $e) {
			$this->line("\t\t\t`user_roles` did not need FKing.");
		}
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
			
			case "assets" :
				file_put_contents($file, "assets");
				$this->importInfinityBoardAssets();
			
			case "config" :
				file_put_contents($file, "config");
				$this->importInfinityBoardConfig();
			
			case "pages" :
				file_put_contents($file, "pages");
				$this->importInfinityBoardPages();
			
			case "tags" :
				file_put_contents($file, "tags");
				$this->importInfinityBoardTags();
			
			case "posts" :
				file_put_contents($file, "posts");
				$this->importInfinityBoardPosts();
			
			break;
			default :
				$this->error("Import state \"{$state}\" invalid.");
		}
		
	}
	
	/**
	 * Imports board attachments.
	 *
	 * @return void
	 */
	public function importInfinityAttachments()
	{
		$this->info("\tImporting attachments ...");
		
		Board::where('board_uri', '>=', "jonimu")->orderBy('board_uri', 'asc')->chunk(1, function($boards)
		{
			foreach ($boards as $board)
			{
				FileAttachment::whereForBoard($board)->forceDelete();
				
				$this->line("\t\tImporting attachments from /{$board->board_uri}/");
				
				$tTable = $this->tcon->table("posts_{$board->board_uri}");
				$storageLinked = 0;
				$attachmentsMade = 0;
				
				// Threads first, replies by id.
				$tTable
					->where('num_files', '>', 0)
					->orderByRaw('thread asc, id asc')
					->chunk(200, function($posts) use (&$board, &$attachmentsMade, &$storageLinked)
				{
					$this->line("\t\t\tImporting 200 more posts's attachments ...");
					$aModels = [];
					$fModels = [];
					$skips   = 0;
					
					// [{
					// 	"name":"1417727856564.png",
					// 	"type":"image\/png",
					// 	"tmp_name":"\/tmp\/php05oN25",
					// 	"error":0,
					// 	"size":13034,
					// 	"filename":"1417727856564.png",
					// 	"extension":"png",
					// 	"file_id":"1423141860833",
					// 	"file":"1423141860833.png",
					// 	"thumb":"1423141860833.jpg",
					// 	"is_an_image":true,
					// 	"hash":"8c63f0b812657c38966ddc7d387a9a4b",
					// 	"width":223,
					// 	"height":200,
					// 	"thumbwidth":223,
					// 	"thumbheight":200,
					// 	"file_path":"b\/src\/1423141860833.png",
					// 	"thumb_path":"b\/thumb\/1423141860833.jpg"
					// }]
					
					// files, num_files, filehash
					foreach ($posts as $post)
					{
						
					}
					
					if (FileAttachment::insert($aModels))
					{
						$attachmentsMade += count($aModels);
					}
				});
				
				$this->line("\t\t\tImported {$storageLinked} files with {$attachmentsMade} attachments.");
			}
		});
	}
	
	/**
	 * Imports board attachments.
	 *
	 * @return void
	 */
	public function importInfinityBoardAssets()
	{
		$this->info("\tImporting board assets ...");
		
		BoardAsset::whereDoesntHave('flagPosts')->forceDelete();
		
		Board::orderBy('board_uri', 'asc')->chunk(1, function($boards)
		{
			foreach ($boards as $board)
			{
				$this->line("\t\tImporting assets from /{$board->board_uri}/");
				$flagsMade = 0;
				$bannersMade = 0;
				
				# FLAGS
				$flagsPath   = "{$this->targetLocation}/static/custom-flags/{$board->board_uri}/";
				$flagSerPath = "{$this->targetLocation}/{$board->board_uri}/flags.ser";
				$flags       = [];
				
				if (file_exists($flagSerPath))
				{
					try
					{
						$flags = @unserialize(@file_get_contents("{$this->targetLocation}/{$board->board_uri}/flags.ser"));
					}
					catch (\Exception $e)
					{
						$this->warn("\t\t\tFailed to unserialize flags.ser");
					}
					
					if (is_array($flags) && count($flags))
					{
						foreach ($flags as $flagFile => $flagName)
						{
							$flag = new File("{$flagsPath}{$flagFile}.png", false);
							
							if ($flag->isReadable())
							{
								$storage = FileStorage::storeUpload($flag);
								
								$asset = $board->assets()->create([
									'file_id'    => $storage->file_id,
									'asset_type' => "board_flags",
									'asset_name' => $flagName,
								]);
								
								++$flagsMade;
							}
						}
					}
				}
				
				# BANNERS
				$bannersPath = "{$this->targetLocation}/static/banners/{$board->board_uri}/";
				
				if (is_readable($bannersPath))
				{
					$banners = array_filter(scandir($bannersPath), function($item) use ($bannersPath) {
						return !is_dir("{$bannersPath}{$item}");
					});
					
					foreach ($banners as $bannerName)
					{
						$banner = new File("{$bannersPath}{$bannerName}", false);
						
						if ($banner->isReadable() && !!FileFacade::get($banner))
						{
							$storage = FileStorage::storeUpload($banner);
							
							$asset = $board->assets()->create([
								'file_id'    => $storage->file_id,
								'asset_type' => "board_banner",
								'asset_name' => null,
							]);
							
							++$bannersMade;
						}
					}
				}
				
				$this->line("\t\tImported {$flagsMade} flags and {$bannersMade} banners.");
			}
		});
	}
	
	/**
	 * Imports configuration files.
	 */
	public function importInfinityBoardConfig()
	{
		$this->info("\tImporting board assets ...");
		
		BoardSetting::truncate();
		
		Board::orderBy('board_uri', 'asc')->chunk(1, function($boards)
		{
			foreach ($boards as $board)
			{
				$this->line("\t\tImporting config from /{$board->board_uri}/");
				
				# CONFIG
				$config      = [];
				$configPath  = "{$this->targetLocation}/{$board->board_uri}/config.php";
				$settings    = [];
				$permissions = [];
				
				if (is_readable($configPath))
				{
					try
					{
						include($configPath);
					}
					catch (\Exception $e)
					{
						$this->warn("\t\tBAD CONFIG!");
					}
				}
				
				foreach ($config as $configName => $configValue)
				{
					$optionName = null;
					$optionValue = null;
					
					switch ($configName)
					{
						# CONTENT RESTRICTIONS
						case "field_disable_name" :
							$optionName  = "postsAllowAuthor";
							$optionValue = !!$configValue;
							break;
						
						case "force_image_op" :
							$optionName  = "threadAttachmentsMin";
							$optionValue = $configValue ? 1 : 0;
							break;
						
						case "force_subject_op" :
							$optionName  = "threadRequireSubject";
							$optionValue = !!$configValue;
							break;
						
						case "max_newlines" :
							$optionName  = "postNewLines";
							$optionValue = max( (int) $configValue, 0 );
							break;
						
						case "min_body" :
							$optionName  = "postMinLength";
							$optionValue = max( (int) $configValue, 0 );
							break;
						
						# POST META
						case "anonymous" :
							$optionName  = "postAnonymousName";
							$optionValue = $configValue ?: null;
							break;
						
						case "country_flags" :
							$optionName  = "postsAuthorCountry";
							$optionValue = !!$configValue;
							break;
						
						case "poster_ids" :
							$optionName  = "postsThreadId";
							$optionValue = !!$configValue;
							break;
						
						# ORIGINALITY ENFORCEMENT
						case "robot_enable" :
							$optionName  = "originalityPosts";
							$optionValue = $configValue ? "boardr9k" : "";
							break;
						
						case "disable_images" :
							$optionName  = "postAttachmentsMax";
							$optionValue = $configValue ? 0 : 5;
							break;
						
						case "image_reject_repost" :
						case "image_reject_repost_in_thread" :
							$optionName  = "originalityImages";
							
							if (isset($config['image_reject_repost']) && $config['image_reject_repost'])
							{
								$optionValue = "board";
							}
							else if (isset($config['image_reject_repost_in_thread']) && $config['image_reject_repost_in_thread'])
							{
								$optionValue = "thread";
							}
							else
							{
								$optionValue = "";
							}
							break;
						
						# EPHEMERALITY
						case "max_pages" :
							$optionName  = "epheDeleteThreadPage";
							$optionValue = max( (int) $configValue, 1 );
							break;
						
						case "reply_limit" :
							$optionName  = "epheLockThreadReply";
							$optionValue = max( (int) $configValue, 1 );
							break;
						
						# PERMISSIONS
						case "tor_posting" :
							$permissions[] = [
								'permission_id' => "board.post.create.thread",
								'value'         => !!$configValue,
							];
							$permissions[] = [
								'permission_id' => "board.post.create.reply",
								'value'         => !!$configValue,
							];
							continue;
						
						case "tor_image_posting" :
							$permissions[] = [
								'permission_id' => "board.image.upload.new",
								'value'         => !!$configValue,
							];
							$permissions[] = [
								'permission_id' => "board.image.upload.old",
								'value'         => true,
							];
							continue;
						
						# CSS
						case "stylesheets" :
							if (is_array($configValue))
							{
								$optionName = "boardCustomCSS";
								$optionValue = "/* Styling imported from Infinity. ~ xoxo Josh */\n\n";
								$cssPath = "{$this->targetLocation}/stylesheets/";
								
								foreach ($configValue as $stylesheet)
								{
									if (is_readable("{$cssPath}{$stylesheet}"))
									{
										$optionValue .= file_get_contents("{$cssPath}{$stylesheet}");
									}
								}
							}
							break;
					}
					
					if (!is_null($optionName))
					{
						$settings[$optionName] = [
							'board_uri'    => $board->board_uri,
							'option_name'  => $optionName,
							'option_value' => $optionValue,
							'is_locked'    => false,
						];
					}
				}
				
				if (count($settings))
				{
					$board->settings()->createMany($settings);
				}
				
				if (count($permissions))
				{
					$role = Role::getUnaccountableRoleForBoard($board);
					$role->permissions()->detach();
					$role->permissionAssignments()->createMany($permissions);
				}
			}
		});
	}
	
	/**
	 * Imports board pages.
	 *
	 * @return void
	 */
	public function importInfinityBoardPages()
	{
		$this->info("\tImporting board pages ...");
		
		Page::truncate();
		Board::orderBy('board_uri', 'asc')->chunk(1, function($boards)
		{
			foreach ($boards as $board)
			{
				$this->line("\t\tImporting pages from /{$board->board_uri}/");
				$tPages = $this->tcon->table("pages")->where('board', $board->board_uri)->get();
				$pagesMade = 0;
				$pages = [];
				$now = Carbon::now();
				
				foreach ($tPages as $tPage)
				{
					if (!$tPage->content)
					{
						continue;
					}
					
					$pages[] = [
						'created_at' => $now,
						'updated_at' => $now,
						'board_uri' => $board->board_uri,
						'name'  => $tPage->name,
						'title' => $tPage->title,
						'body'  => $tPage->content,
					];
					
					++$pagesMade;
				}
				
				Page::insert($pages);
				$this->line("\t\tCreated {$pagesMade} pages.");
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
				$tagTxt = substr((string)$tag->tag, 0, 32);
				
				if (!isset($boardTags[$tag->uri]))
				{
					$boardTags[$tag->uri] = [];
				}
				
				if (!isset($tagModels[$tagTxt]))
				{
					$tagModels[$tagTxt] = BoardTag::firstOrCreate([
						'tag' => $tagTxt,
					]);
				}
				
				$boardTags[$tag->uri] = $tagModels[$tagTxt];
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
	
	/**
	 * Imports posts.
	 *
	 * @return void
	 */
	public function importInfinityBoardPosts()
	{
		$this->info("\tImporting posts ...");
		
		Board::whereIn('board_uri', ['next'])->orderBy('board_uri', 'asc')->chunk(1, function($boards)
		{
			foreach ($boards as $board)
			{
				$this->line("\t\tTruncating Infinity Next posts for /{$board->board_uri}/");
				$post_ids = $board->posts()->withTrashed()->select('post_id')->get()->pluck('post_id');
				FileAttachment::whereIn('post_id', $post_ids)->forceDelete();
				$board->posts()->withTrashed()->forceDelete();
				$board->posts_total = 0;
				$board->save();
				
				$this->tcon->unprepared(DB::raw("LOCK TABLES posts_{$board->board_uri} WRITE"));
				file_get_contents("http://banners.8ch.net/status/{$board->board_uri}/migrating");
				
				$this->line("\t\tImporting posts from /{$board->board_uri}/");
				
				$tTable       = $this->tcon->table("posts_{$board->board_uri}");
				
				// Post Info
				$postsMade    = 0;
				$validThreads = [];
				
				// Attachment Info
				$storageLinked   = 0;
				$attachmentsMade = 0;
				
				// Threads first, replies by id.
				$query = $tTable->orderByRaw('thread asc, id asc');
				$bar = $this->output->createProgressBar( $tTable->orderByRaw('thread asc, id asc')->count() );
				$query->chunk(200, function($posts) use (&$bar, &$validThreads, &$board, &$postsMade, &$attachmentsMade)
				{
					$models = [];
					
					// thread, subject, email, name, trip, capcode, body, body_nomarkup, time, bump, files, num_files, filehash, password, ip, sticky, locked, cycle, sage, embed, edited_at
					// post_id | board_uri | board_id | reply_to | reply_to_board_id | reply_count | reply_last | bumped_last | created_at | updated_at | updated_by | deleted_at | stickied | stickied_at | bumplocked_at | locked_at | author_ip | author_id | author_country | author_ip_nulled_at | author | insecure_tripcode | capcode_id | adventure_id | subject | email | password | body | body_parsed | body_parsed_at | body_html | featured_at | body_too_long | body_parsed_preview | flag_id | reply_file_count
					foreach ($posts as $post)
					{
						$model = $this->importInfinityPost($post, $board, $validThreads);
						
						++$postsMade;
						
						// Have to save threads so we have a post_id.
						if (!$post->thread)
						{
							$model = new Post($model);
							$model->save();
							$validThreads[$post->id] = $model;
						}
						else
						{
							$models[] = $model;
						}
					}
					
					foreach ($models as $model)
					{
						$post = new Post($model);
						$post->save();
						$attachmentsMade += $this->importInfinityPostAttachments($post, $board);
					}
					
					$bar->advance( count($posts) );
				});
				
				$this->tcon->unprepared(DB::raw("UNLOCK TABLES"));
				file_get_contents("https://banners.8ch.net/status/{$board->board_uri}/horizon");
				
				$this->line("\n\t\t\tMade {$postsMade} posts with {$attachmentsMade} attachments for /{$board->board_uri}/.");
			}
		});
	}
	
	public function importInfinityPost($post, Board &$board, &$threads)
	{
		$thread = null;
		
		if (!$post->thread)
		{
			$thread = null;
		}
		else if (isset($threads[$post->thread]))
		{
			$thread = $threads[$post->thread];
		}
		else
		{
			return false;
		}
		
		$createdAt  = Carbon::now();
		$editedAt   = $createdAt;
		$bumpedLast = $createdAt;
		
		// Yes. Vichan database records for time can be malformed.
		// Handle them carefully.
		try {
			$createdAt = Carbon::createFromTimestampUTC($post->time);
		} catch (\Exception $e) { }
		
		try {
			if ($post->edited_at)
			{
				$editedAt = Carbon::createFromTimestampUTC($post->edited_at);
			}
		} catch (\Exception $e) { }
		
		try {
			if ($post->bump)
			{
				$bumpedLast = Carbon::createFromTimestampUTC($post->bump);
			}
		} catch (\Exception $e) { }
		
		return [
			'board_uri'           => $board->board_uri,
			'board_id'            => (int) $post->id,
			'reply_to'            => $thread ? (int) $thread->post_id : null,
			'reply_to_board_id'   => $thread ? (int) $thread->board_id : null,
			
			'created_at'          => $createdAt,
			'updated_at'          => $editedAt,
			'updated_by'          => null,
			'deleted_at'          => null,
			'bumped_last'         => $bumpedLast,
			
			'stickied'            => !($post->thread || !$post->sticky),
			'stickied_at'         => $post->thread || !$post->sticky ? null : $createdAt,
			'locked_at'           => $post->thread || !$post->locked ? null : $createdAt,
			
			'body'                => null,
			'body_parsed'         => null,
			'body_parsed_at'      => null,
			'body_html'           => (string) $post->body,
			'body_too_long'       => null,
			'body_parsed_preview' => null,
			
			'subject'             => $post->subject ?: null,
			'author'              => $post->name ?: null,
			'author_id'           => null,
			'author_ip'           => $post->ip ? new IP($post->ip) : null,
			'email'               => (string) $post->email,
			'insecure_tripcode'   => $post->trip ? ltrim("{$post->trip}", "!") : null,
			'password'            => null,
		];
	}
	
	public function importInfinityPostAttachments($post, Board &$board)
	{
		$post_id = $post->post_id;
		
		if (!$post_id)
		{
			return 0;
		}
		
		$attachments = @json_decode($post->files, true);
		
		if (!is_array($attachments))
		{
			return 0;
		}
		
		$aModels = [];
		
		foreach ($attachments as $aIndex => $attachment)
		{
			if (isset($attachment['error']) && $attachment['error'])
			{
				continue;
			}
			
			if (!isset($attachment['file_path']) || !isset($attachment['thumb_path']))
			{
				continue;
			}
			
			$storage = null;
			$path    = "{$this->targetLocation}/{$attachment['file_path']}";
			$thumb   = "{$this->targetLocation}/{$attachment['thumb_path']}";
			
			if (file_exists($path))
			{
				if (!isset($attachment['hash']))
				{
					$attachment['hash'] = md5(file_get_contents($path));
				}
				
				$storage = FileStorage::getHash($attachment['hash']);
				
				if (!$storage)
				{
					$height = null;
					$width = null;
					
					if (isset($attachment['width']) && isset($attachment['height']))
					{
						$height = $attachment['height'];
						$width = $attachment['width'];
					}
					
					$storage = new FileStorage([
						'hash'              => $attachment['hash'],
						'banned'            => false,
						'filesize'          => $attachment['size'],
						'file_width'        => $width,
						'file_height'       => $height,
						'mime'              => $attachment['type'],
						'meta'              => null,
						'first_uploaded_at' => Carbon::now(),
						'last_uploaded_at'  => Carbon::now(),
						'upload_count'      => 1,
					]);
					
					Storage::makeDirectory($storage->getDirectory());
					
					if (!$storage->hasFile())
					{
						symlink($path, $storage->getFullPath());
					}
					
					if ($attachment['thumbwidth'] && file_exists($thumb))
					{
						$storage->has_thumbnail    = true;
						$storage->thumbnail_width  = $attachment['thumbwidth'];
						$storage->thumbnail_height = $attachment['thumbheight'];
						
						Storage::makeDirectory($storage->getDirectoryThumb());
						
						if (!$storage->hasThumb())
						{
							symlink($thumb, $storage->getFullPathThumb());
						}
					}
					
					$storage->save();
					++$storageLinked;
				}
				
				if ($storage && $storage->exists)
				{
					$aModel = [
						'post_id'    => $post_id,
						'file_id'    => $storage->file_id,
						'filename'   => $attachment['filename'],
						'is_spoiler' => false,
						'is_deleted' => false,
						'position'   => $aIndex,
					];
					
					$aModels[] = $aModel;
				}
				else
				{
					++$skips;
				}
			}
		}
		
		FileAttachment::insert($aModels);
		return count($aModels);
	}
	
	/**
	 * Imports users and creates roles based on addition data in that row.
	 *
	 * @return void
	 */
	public function importInfinityRolesAndBoards()
	{
		# BORROW SEEDERS
		require base_path() . "/database/seeds/OptionSeeder.php";
		require base_path() . "/database/seeds/PermissionSeeder.php";
		require base_path() . "/database/seeds/RoleSeeder.php";
		
		# DESTROY SEQUENCE
		if (DB::connection() instanceof \Illuminate\Database\PostgresConnection)
		{
			$this->comment("\tDropping role sequence.");
			DB::statement("DROP SEQUENCE IF EXISTS roles_role_id_seq CASCADE;");
		}
		
		$PermissionSeeder = new \PermissionSeeder;
		$PermissionSeeder->setCommand($this);
		$PermissionSeeder->run();
		
		$PermissionGroupSeeder  = new \PermissionGroupSeeder;
		$PermissionGroupSeeder->setCommand($this);
		$PermissionGroupSeeder->run();
		
		$OptionSeeder = new \OptionSeeder;
		$OptionSeeder->setCommand($this);
		$OptionSeeder->run();
		
		$OptionGroupSeeder  = new \OptionGroupSeeder;
		$OptionGroupSeeder->setCommand($this);
		$OptionGroupSeeder->run();
		
		$RoleSeeder = new \RoleSeeder;
		$RoleSeeder->setCommand($this);
		$RoleSeeder->runMaster();
		
		$RolePermissionSeeder = new \RolePermissionSeeder;
		$RolePermissionSeeder->setCommand($this);
		$RolePermissionSeeder->run();
		
		\Artisan::call('cache:clear');
		
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
					
					if (!$tBoard->public_bans || !$tBoard->public_logs || $tBoard->public_logs == 2)
					{
						$role = Role::getAnonymousRoleForBoard($hBoard);
						$perms = [];
						
						if (!$tBoard->public_bans)
						{
							$perms[] = [
								'permission_id' => "board.bans",
								'value'         => false,
							];
						}
						
						if (!$tBoard->public_logs || $tBoard->public_logs == 2)
						{
							$perms[] = [
								'permission_id' => "board.logs",
								'value'         => false,
							];
						}
						
						$role->permissionAssignments()->createMany($perms);
					}
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
	
}

}

namespace {
	define("JANITOR",  10);
	define("MOD",      20);
	define("ADMIN",    30);
	define("DISABLED", 9);

	function event_handler() {
		// Nothing. Vichan issue.
	}
}
