<?php

namespace App\Observers;

use App\BoardAdventure;
use App\FileStorage;
use App\Post;
use App\PostAttachment;
use App\PostCite;
use App\Events\PostWasAdded;
use App\Events\PostWasDeleted;
use App\Events\PostWasModified;
use App\Events\ThreadNewReply;
use App\Filesystem\Upload;
use App\Jobs\PostCreate;
use App\Support\Geolocation;
use App\Support\IP;
use App\Support\ContentFormatter;
use Cache;
use DB;
use Request;

class PostObserver
{
    /**
     * Handles model after create (non-existant save).
     *
     * @param \App\Post $post
     *
     * @return bool
     */
    public function created(Post $post)
    {
        $board = $post->board;
        $thread = $post->thread;

        // Optionally, the OP of this thread needs a +1 to reply count.
        if ($thread instanceof Post) {
            // We're not using the Model for this because it fails under high volume.
            $threadNewValues = [
                'updated_at' => $thread->updated_at,
                'reply_last' => $post->created_at,
                'reply_count' => $thread->replies()->count(),
                'reply_file_count' => $thread->replyFiles()->count(),
            ];

            if (!$post->isBumpless() && !$thread->isBumplocked()) {
                $threadNewValues['bumped_last'] = $post->created_at;
            }

            DB::table('posts')
                ->where('post_id', $thread->post_id)
                ->update($threadNewValues);
        }

        // Process uploads.
        $uploads = [];

        // Check file uploads.
        if (is_array($files = Request::file('files'))) {
            $uploads = array_filter($files);

            if (count($uploads) > 0) {
                foreach ($uploads as $uploadIndex => $upload) {
                    if (file_exists($upload->getPathname())) {
                        FileStorage::createAttachmentFromUpload($upload, $post);
                    }
                }
            }
        }
        elseif (is_array($files = Request::input('files'))) {
            $uniques = [];
            $hashes = $files['hash'];
            $names = $files['name'];
            $spoilers = isset($files['spoiler']) ? $files['spoiler'] : [];

            $storages = FileStorage::whereIn('hash', $hashes)->get();

            foreach ($hashes as $index => $hash) {
                if (!isset($uniques[$hash])) {
                    $uniques[$hash] = true;
                    $storage = $storages->where('hash', $hash)->first();

                    if ($storage->exists && is_null($storage->banned_at)) {
                        $uploader = new Upload($storage);
                        $uploader->process();

                        $spoiler = isset($spoilers[$index]) ? $spoilers[$index] == 1 : false;

                        $fileName = pathinfo($names[$index], PATHINFO_FILENAME);
                        $fileExt = $storage->guessExtension();

                        $attachment = new PostAttachment;
                        $attachment->post_id = $post->post_id;
                        $attachment->filename = urlencode("{$fileName}.{$fileExt}");
                        $attachment->is_spoiler = (bool) $spoiler;

                        $attachment->setRelation('file', $storage);
                        $attachment->file_id = $storage->file_id;

                        $thumbnail = $uploader->getThumbnail();
                        if ($thumbnail instanceof FileStorage) {
                            $attachment->setRelation('thumbnail', $thumbnail);
                            $attachment->thumbnail_id = $thumbnail->file_id;
                        }

                        $attachment->position = $index;
                        $uploads[] = $attachment;
                    }
                }
            }

            $post->attachments()->saveMany($uploads);
            FileStorage::whereIn('hash', $hashes)->increment('upload_count');
        }

        // Optionally, we also expend the adventure.
        $adventure = BoardAdventure::getAdventure($board);

        if ($adventure) {
            $post->adventure_id = $adventure->adventure_id;
            $adventure->expended_at = $post->created_at;
            $adventure->save();
        }
        // Add dice rolls.
        // Because of how dice rolls work, we don't ever remove them and only
        // create them with the post, not on update.
        $throws = $post->getDiceFromText();
        $dice   = collect();

        foreach ($throws as $throw)
        {
            $post->dice()->save($throw['throw'], [
                'command_text' => $throw['command_text'],
                'order' => $throw['order'],
            ]);
        }

        // NOTE: The transaction starts in creating()!
        DB::commit();

        if (!is_null(user()) && user()->isAccountable()) {
            Cache::lock('posting_now_'.$post->author_ip->toLong(), 10)->release();
        }

        PostCreate::dispatch($post);

        return true;
    }

    /**
     * Checks if this model is allowed to create (non-existant save).
     *
     * @param  \App\Post  $post
     *
     * @return bool
     */
    public function creating(Post $post)
    {
        $board = $post->board;
        $thread = $post->thread;

        $post->board_uri = $board->board_uri;
        $post->author_ip = $post->author_ip ?? new IP;
        $post->author_country = $board->getConfig('postsAuthorCountry', false) ? new Geolocation : null;
        $post->reply_last = $post->freshTimestamp();
        $post->bumped_last = $post->reply_last;
        $post->setCreatedAt($post->reply_last);
        $post->setUpdatedAt($post->reply_last);

        if (!is_null($thread) && !($thread instanceof Post)) {
            $thread = $board->getLocalThread($thread);
        }

        if (!is_null(user()) && user()->isAccountable()) {
            if (!Cache::lock('posting_now_'.$post->author_ip->toLong(), 10)->get()) {
                return abort(429, "Slow down.");
            }

            // Cache what time we're submitting our post for flood checks.
            Cache::put('last_post_for_'.$post->author_ip->toLong(), $post->created_at->timestamp, now()->addHour());

            if ($thread instanceof Post) {
                $post->reply_to = $thread->post_id;
                $post->reply_to_board_id = $thread->board_id;

                Cache::put('last_thread_for_'.$post->author_ip->toLong(), $post->created_at->timestamp, now()->addHour());
            }
        }
        else {
            $post->author_ip = null;

            if ($thread instanceof Post) {
                $post->reply_to = $thread->post_id;
                $post->reply_to_board_id = $thread->board_id;
            }
        }

        // Handle tripcode, if any.
        if (preg_match('/^([^#]+)?(##|#)(.+)$/', $post->author, $match)) {
            // Remove password from name.
            $post->author = $match[1];
            // Whether a secure tripcode was requested, currently unused.
            $secure_tripcode_requested = ($match[2] == '##');
            // Convert password to tripcode, store tripcode hash in DB.
            $post->insecure_tripcode = ContentFormatter::formatInsecureTripcode($match[3]);
        }

        // Ensure we're using a valid flag.
        if (!$post->flag_id || !$board->hasFlag($post->flag_id)) {
            $post->flag_id = null;
        }

        // Store the post in the database.
        // NOTE: This ends in created!
         DB::beginTransaction();
        // The objective of this transaction is to prevent concurrency issues in the database
        // on the unique joint index [`board_uri`,`board_id`] which is generated procedurally
        // alongside the primary autoincrement column `post_id`.

        // First instruction is to add +1 to posts_total and set the last_post_at on the Board table.
        DB::table('boards')
            ->where('board_uri', $post->board_uri)
            ->increment('posts_total', 1, [
                'last_post_at' => $post->reply_last,
            ]);

        // Second, we record this value and lock the table.
        $boards = DB::table('boards')
            ->where('board_uri', $post->board_uri)
            ->lockForUpdate()
            ->select('posts_total')
            ->get();

        $posts_total = $boards[0]->posts_total;

        // Third, we store a unique checksum for this post for duplicate tracking.
        $board->checksums()->create([
            'checksum' => $post->getChecksum(),
        ]);

        // We set our board_id and save the post.
        $post->board_id = $posts_total;
        $post->author_id = $post->makeAuthorId();
        $post->password = $post->makePassword($post->password);

        return !is_null($post->board_id);
    }

    /**
     * Handles model after delete (pre-existing hard or soft deletion).
     *
     * @param \App\Post $post
     *
     * @return bool
     */
    public function deleted($post)
    {
        // After a post is deleted, update OP's reply count.
        if (!is_null($post->reply_to)) {
            $lastReply = $post->thread->getReplyLast();

            if ($lastReply) {
                $post->thread->reply_last = $lastReply->created_at;
            } else {
                $post->thread->reply_last = $post->thread->created_at;
            }

            $post->thread->reply_count -= 1;
            $post->thread->save();
        }

        // Update any posts that reference this one.
        $citedBy = $post->citedByPosts();
        $citedBy->update([
            'body_parsed' => null,
            'body_parsed_at' => null,
        ]);
        $citedBy->detach();

        // Remove this item from the cache.
        $post->forget();

        // Fire event.
        event(new PostWasDeleted($post));

        return true;
    }

    /**
     * Checks if this model is allowed to delete (pre-existing deletion).
     *
     * @param \App\Post $post
     *
     * @return bool
     */
    public function deleting($post)
    {
        // When deleting a post, delete its children.
        Post::replyTo($post->post_id)->delete();

        // Clear authorshop information.
        $post->author_ip = null;
        $post->author_ip_nulled_at = now();
        $post->save();

        return true;
    }

    /**
     * Handles model after save (pre-existing or non-existant save).
     *
     * @param \App\Post $post
     *
     * @return bool
     */
    public function saved(Post $post)
    {
        ## TODO ## This should only happen when the body is changed, why does this happen every save?
        // Rebuild citation relationships.

        // Clear citations.
        $post->cites()->delete();

        // Readd citations.
        $cited = $post->getCitesFromText();
        $cites = [];

        foreach ($cited['posts'] as $citedPost) {
            $cites[] = new PostCite([
                'post_board_uri' => $post->board_uri,
                'post_board_id' => $post->board_id,
                'cite_id' => $citedPost->post_id,
                'cite_board_uri' => $citedPost->board_uri,
                'cite_board_id' => $citedPost->board_id,
            ]);
        }

        foreach ($cited['boards'] as $citedBoard) {
            $cites[] = new PostCite([
                'post_board_uri' => $post->board_uri,
                'post_board_id' => $post->board_id,
                'cite_board_uri' => $citedBoard->board_uri,
            ]);
        }

        if (count($cites) > 0) {
            $post->cites()->saveMany($cites);
        }

        return true;
    }

    /**
     * Checks if this model is allowed to save (pre-existing or non-existant save).
     *
     * @param \App\Post $post
     *
     * @return bool
     */
    public function saving(Post $post)
    {
        return true;
    }

    /**
     * Handles model after update (pre-existing save).
     *
     * @param \App\Post $post
     *
     * @return bool
     */
    public function updated(Post $post)
    {
        // Fire event, which clears cache among other things.
        event(new PostWasModified($post));

        return true;
    }

    /**
     * Checks if this model is allowed to update (pre-existing save).
     *
     * @param \App\Post $post
     *
     * @return bool
     */
    public function updating(Post $post)
    {
        return true;
    }
}
