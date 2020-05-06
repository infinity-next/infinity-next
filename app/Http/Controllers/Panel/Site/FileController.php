<?php

namespace App\Http\Controllers\Panel\Site;

use App\Ban;
use App\FileStorage;
use App\PostAttachment;
use App\Events\FileWasBanned;
use App\Http\Controllers\Panel\PanelController;
use App\Http\Controllers\SendsFilesTrait as SendsFiles;
use Request;

/**
 * File management tools for the site.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class FileController extends PanelController
{
    use SendsFiles;

    const VIEW_INDEX = 'panel.file.index';
    const VIEW_SHOW = 'panel.file.show';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.site';

    /**
     * Show all recent files.
     */
    public function index()
    {
        $this->authorize('ban-file');

        $files = FileStorage::orderBy('last_uploaded_at', 'desc')
            ->whereDoesntHave('thumbnailPivots', function($query) {
                $query->select(\DB::raw('1'));
            })
            ->where('last_uploaded_at', '>', now()->subHours(8))
            ->whereNull('banned_at')
            ->whereNull('fuzzybanned_at')
            ->limit(300)
            ->get();

        return $this->makeView(static::VIEW_INDEX, [
            'files' => $files,
        ]);
    }

    /**
     * Sends the attachment data
     */
    public function send(FileStorage $file)
    {
        $this->authorize('ban-file');

        return $this->sendFile($file);
    }

    /**
     * Shows information about a file.
     */
     public function show(FileStorage $file)
     {
         $this->authorize('ban-file');

         return $this->makeView(static::VIEW_SHOW, [
             'file' => $file,
         ]);
     }

     public function delete(FileStorage $file)
     {
         $this->authorize('ban-file');
         $action = Request::input('action');

         $ban = false;
         $fuzzyban = false;
         $bans = collect([]);

         switch ($action) {
            case "fuzzyban" :
                $fuzzyban = true;
            case "ban" :
                $ban = true;
            break;
         }

         if (!$ban) {
            $file->posts()->withTrashed()->each(function ($post) use ($file) {
                broadcast(new FileWasBanned($post, $file));
                $post->delete();
            });
        }
        else {
            $file->banned_at = now();
            if ($fuzzyban) {
                $file->fuzzybanned_at = $file->banned_at;
            }

            // NOTE: We are using withTrashed() here so if we need to delete
            // multiple attachments on a single post, they all broadcast correctly.
            $file->posts()->withTrashed()->each(function ($post) use ($bans, $file, $fuzzyban) {
                if (!$post->trashed()) {
                    broadcast(new FileWasBanned($post, $file));
                }

                if (!is_null($post->author_ip) && !$bans->contains('ban_ip_start', $post->author_ip)) {
                    $bans->add([
                        'ban_ip_start' => $post->author_ip,
                        'ban_ip_end' => $post->author_ip,
                        'board_uri' => null,
                        'expires_at' => now()->addDays($fuzzyban ? 7 : 1),
                        'mod_id' => user()->user_id,
                        'post_id' => $post->post_id,
                        'justification' => "Posting banned image.",
                    ]);
                }

                $post->delete();
            });
         }

         // apply the storage ban
         $file->save();

         // apply the ip ban(s)
         $bans->each(function ($ban) {
             Ban::create($ban);
         });

         // delete all thumbnails and their storage object
         $file->thumbnails->each(function($thumbnail) {
             $thumbnail->deleteFile();
             $thumbnail->forceDelete();
         });

         // delete off harddrive
         $file->deleteFile();

         return redirect()->route('panel.site.files.index');
     }
}
