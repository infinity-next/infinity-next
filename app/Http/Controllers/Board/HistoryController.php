<?php

namespace App\Http\Controllers\Board;

use App\Board;
use App\Http\Controllers\Controller;
use App\Post;

/**
 * User board history.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class HistoryController extends Controller
{
    const VIEW_HISTORY = 'history';

    public function list(Board $board, Post $post)
    {
        if (!$this->user->canViewHistory($post)) {
            return abort(403);
        }

        if (is_null($post->author_ip)) {
            return abort(400);
        }

        $posts = $board->posts()
            ->with('op')
            ->withEverything()
            ->where('author_ip', $post->author_ip)
            ->orderBy('post_id', 'desc')
            ->paginate(15);

        foreach ($posts as $item) {
            $item->setRelation('board', $board);
        }

        return $this->makeView(static::VIEW_HISTORY, [
            'posts' => $posts,
            'multiboard' => false,
            'ip' => ip_less($post->author_ip->toText()),
        ]);
    }
}
