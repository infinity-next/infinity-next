<?php

namespace App\Http\Controllers\Panel\Site;

use App\Ban;
use App\FileAttachment;
use App\FileStorage;
use App\Http\Controllers\Panel\PanelController;
use App\Http\SendsFilesTrait as SendsFiles;
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
        $this->authorize('admin-config');

        $files = FileStorage::orderBy('last_uploaded_at', 'desc')
            ->where('last_uploaded_at', '>', now()->subDay())
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
        $this->authorize('admin-config');

        return $this->sendFile($file);
    }

    /**
     * Shows information about a file.
     */
     public function show(FileStorage $file)
     {
         $this->authorize('admin-config');

         return $this->makeView(static::VIEW_SHOW, [
             'file' => $file,
         ]);
     }

     public function delete(FileStorage $file)
     {
         $this->authorize('admin-config');
         $action = Request::input('action');

         $ban = false;
         $fuzzyban = false;

         if ($action == "ban") {
             $ban = true;
         }
         elseif ($action == "fuzzyban") {
             $ban = true;
             $fuzzyban = true;
         }

         if (!$ban) {
             FileAttachment::where('file_id', $file->file_id)
                ->update(['is_deleted' => true]);
        }
        else {
            $file->banned = true;
            $file->save();

            $bans = collect([]);
            $file->posts()->each(function ($post) use ($bans) {
                if (!is_null($post->author_ip) && !$bans->contains('ban_ip_start', $post->author_ip)) {
                    $bans->add([
                        'ban_ip_start' => $post->author_ip,
                        'ban_ip_end' => $post->author_ip,
                        'board_uri' => null,
                        'expires_at' => now()->addDays(7),
                        'mod_id' => user()->user_id,
                        'post_id' => $post->post_id,
                        'justification' => "Posting banned image.",
                    ]);
                }

                $post->delete();
            });

            $bans->each(function ($ban) {
                Ban::create($ban);
            });
         }

         if ($fuzzyban) {
             ## TODO ##
         }

         $file->deleteFile();

         return redirect()->route('panel.site.files.index');
     }
}
