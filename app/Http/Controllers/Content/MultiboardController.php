<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Post;
use Input;

/**
 * Renders a stream of content from all boards.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class MultiboardController extends Controller
{
    const VIEW_OVERBOARD = 'overboard';

    protected function getThreads()
    {
        // Pass a variable amount of information into the parent method provided
        // by the Post class.
        return call_user_func_array(
            [
                Post::class,
                'getThreadsForOverboard',
            ],
            func_get_args()
        );
    }

    public function getOverboard()
    {
        $threads = $this->getThreads(max(1, Input::get('page', 1)));

        return $this->view(static::VIEW_OVERBOARD, [
            'threads' => $threads,
        ]);
    }
}
