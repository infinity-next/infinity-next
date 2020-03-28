<?php

namespace App\Http\Controllers\Panel;

use App\Board;
use App\Post;
use App\Support\IP;

/**
 * User post history.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class HistoryController extends PanelController
{
    const VIEW_HISTORY = 'history';

    public function list($ip)
    {
        $this->authorize('global-history');

        if (is_null($ip)) {
            return abort(404);
        }

        try {
            $ip = new IP($ip);
        }
        catch (\InvalidArgumentException $e) {
            return abort(404);
        }
        catch (\Exception $e) {
            throw $e;
        }

        $posts = Post::with('op', 'board', 'board.assets')
            ->withEverything()
            ->where('author_ip', $ip)
            ->orderBy('post_id', 'desc')
            ->paginate(15);

        return $this->view(static::VIEW_HISTORY, [
            'posts' => $posts,
            'ip' => $ip,
        ]);
    }
}
