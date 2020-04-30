<?php

namespace App\Http\Controllers\API\Content;

use App\Post;
use App\Contracts\ApiController as ApiContract;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\Content\BoardlistController as ParentController;
use Carbon\Carbon;
use DB;
use Request;

class WatcherController extends ParentController implements ApiContract
{
    use ApiController;

    /**
     * Returns some very rudimentary information about threads.
     *
     * @return Response|JSON
     */
    public function fetch()
    {
        // global post id : last viewed timestamp
        $input = Request::input('threads');
        $response = [];

        if (is_array($input)) {
            $threads = array_keys($input);
            $timestamps = array_values($input);
            $response = [];

            $datetime = Carbon::createFromTimestamp(min($timestamps));
            $posts = Post::whereIn('reply_to', $threads)
                ->whereDate('created_at', '>', $datetime)
                ->whereTime('created_at', '>', $datetime)
                ->get('reply_to', 'created_at');

            foreach ($input as $thread => $timestamp) {
                $response[$thread] = $posts->where('reply_to', $thread)->count();
            }
        }

        return $response;
    }
}
