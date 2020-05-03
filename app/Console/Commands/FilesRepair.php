<?php

namespace App\Console\Commands;

use App\PostAttachment;
use App\Filesystem\Upload;
use Illuminate\Console\Command;
use Cache;
use Storage;

/**
 * @category   Command
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since  0.6.0
 */
class FilesRepair extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'files:repair-thumbnails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rebuild thumbnails.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->comment("Fixing post attachments!");
        PostAttachment::whereHas('post')->whereHas('file')->whereHas('thumbnail')->with('post', 'file', 'file.thumbnails', 'thumbnail', 'thumbnail')->chunk(100, function ($attachments) {
            foreach ($attachments as $attachment) {
                $thumb = $attachment->thumbnail;

                if (!is_null($thumb) && $thumb->hasFile()) {
                    if (!$attachment->file->thumbnails->contains($thumb)) {
                        $this->line("Reattaching thumbnail to its source.");
                        $attachment->file->thumbnails()->save($thumb);
                    }
                    continue;
                }

                $this->warn("PostAttachment {$attachment->post_id}.{$attachment->position} is broken.");

                foreach ($attachment->file->thumbnails as $thumbnail) {
                    if ($thumbnail->hasFile()) {
                        $thumb = $thumbnail;
                        $attachment->thumbnail_id = $thumbnail->file_id;
                        $attachment->save();
                        $this->line("Found an existing thumbnail to use, nice!");
                        break;
                    }
                }

                if (!is_null($thumb) && $thumb->hasFile()) {
                    continue;
                }

                $this->info("Processing a new thumbnail.");
                $uploader = new Upload($attachment->file);
                $newThumbs = $uploader->processThumbnails();

                if ($newThumbs->count() > 0) {
                    $this->line("We made a new one!");
                    $newThumb = $newThumbs->first();
                    $attachment->thumbnail()->associate($newThumb);
                    $attachment->save();

                    if (!$newThumb->hasFile()) {
                        $newThumb->putFile();
                    }

                    if (!$newThumb->hasFile()) {
                        $this->error("New thumb does not exist despite trying to make a new one.");
                    }

                    $attachment->file->thumbnails()->saveMany($newThumbs);
                    continue;
                }

                $this->warn("Couldn't create a new thumbnail!");
            }
        });
    }
}
