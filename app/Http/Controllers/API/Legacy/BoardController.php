<?php

namespace App\Http\Controllers\API\Legacy;

use App\Board;
use App\Post;
use App\Http\Controllers\Controller as ParentController;

class BoardController extends ParentController
{
    /**
     * Show the board index for the user.
     * This is usually the last few threads, depending on the optional page
     * parameter, which determines the thread offset.
     *
     * @param \App\Board $board
     * @param int        $page
     *
     * @return Response
     */
    public function getIndex(Board $board, $page = 1)
    {
        // Determine what page we are on.
        $pages = $board->getPageCount();

        // Clamp the page to real values.
        if ($page <= 0) {
            $page = 1;
        } elseif ($page > $pages) {
            return abort(404);
        }

        // Determine if we have a next/prev button.
        $pagePrev = ($page > 1) ? $page - 1 : false;
        $pageNext = ($page < $pages) ? $page + 1 : false;

        // Load our list of threads and their latest replies.
        $threads = $board->getThreadsForIndex($page);
        $response = ['threads' => []];

        foreach ($threads as $thread) {
            $threadArray = ['posts' => []];

            $posts = collect([$thread])->merge($thread->replies);

            foreach ($posts as $post) {
                $threadArray['posts'][] = $this->postToJson($post);
            }

            $response['threads'][] = $threadArray;
        }

        return response()->json($response);
    }

    /**
     * Returns a thread and its replies.
     *
     * @param \App\Board $board
     * @param \App\Post  $thread
     *
     * @return Response
     */
    public function getThread(Board $board, Post $thread)
    {
        $posts = collect([$thread])->merge($thread->replies);
        $response = ['posts' => []];

        foreach ($posts as $post) {
            $response['posts'][] = $this->postToJson($post);
        }

        return response()->json($response);
    }

    /**
     * Returns a thread and its replies.
     *
     * @param \App\Board $board
     *
     * @return Response
     */
    public function getThreads(Board $board)
    {
        // Determine what page we are on.
        $pages = $board->getPageCount();

        $response = [];

        for ($i = 0; $i < $pages; ++$i) {
            $pageArray = ['page' => $i, 'threads' => []];
            $threads = $board->getThreadsForIndex($i);

            foreach ($threads as $thread) {
                $pageArray['threads'][] = [
                    'no' => (int) $thread->board_id,
                    'last_modified' => (int) $thread->bumped_last->timestamp,
                ];
            }

            $response[] = $pageArray;
        }

        return response()->json($response);
    }

    /**
     * Converts a single post in standard legacy API output.
     *
     * @param \App\Post $post
     *
     * @return array
     */
    protected function postToJson(Post $post)
    {
        // Actual post information.
        $postArray = [
            'no' => (int) $post->board_id,
            'resto' => (int) $post->reply_to_board_id ?: 0,

            'sticky' => (bool) $post->isStickied(),
            'locked' => (bool) $post->isLocked(),
            'cyclical' => (bool) $post->isCyclic(),

            'name' => (string) $post->author,
            'sub' => (string) $post->subject,
            'com' => (string) $post->getBodyFormatted(),

            'time' => (int) $post->created_at->timestamp,
            'last_modified' => (int) $post->bumped_last->timestamp,

            'omitted_posts' => 0,
            'omitted_images' => 0,
        ];

        // Attachment information.
        foreach ($post->attachments as $attachmentIndex => $attachment) {
            $attachmentArray = [
                'filename' => $attachment->getBaseFileName(),
                'ext' => '.'.$attachment->getExtension(),
                'tim' => $attachment->getFileName('%t-%i'),
                'md5' => base64_encode(hex2bin($attachment->hash)),

                'fsize' => $attachment->filesize,

                'tn_h' => $attachment->thumbnail_height,
                'tn_w' => $attachment->thumbnail_width,
                'h' => $attachment->file_height,
                'w' => $attachment->file_width,
            ];

            if ($attachmentIndex === 0) {
                $postArray = array_merge($postArray, $attachmentArray);
            } else {
                if (!isset($postArray['extra_files'])) {
                    $postArray['extra_files'] = [];
                }

                $postArray['extra_files'][] = $attachmentArray;
            }
        }

        return $postArray;
    }
}
