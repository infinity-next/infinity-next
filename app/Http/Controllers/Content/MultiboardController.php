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

    public function getOverboardWithWorksafe($worksafe)
    {
        return $this->getOverboard($worksafe, null);
    }

    public function getOverboardWithBoards($boards)
    {
        return $this->getOverboard(null, $boards);
    }

    public function getOverboard($worksafe = null, $boards = null)
    {
        $includes = [];
        $excludes  = [];

        if (!is_null($boards)) {
            // Break apart a board filter string.
            // Examples: +want -donotwant +want+alsowant-notthis
            $matchCount = preg_match_all(
                '/((?P<sign>\+|-)(?P<board_uri>[a-z0-9]{1,32}))/',
                $boards,
                $matches
            );

            for ($x = 0; $x < $matchCount; ++$x) {
                $sign = $matches['sign'][$x];
                $board_uri = $matches['board_uri'][$x];

                if ($sign === "-") {
                    $excludes[] = $board_uri;
                } else {
                    $includes[] = $board_uri;
                }
            }
        }

        $threads = $this->getThreads(
            max(1, Input::get('page', 1)),
            is_null($worksafe) ? $worksafe : $worksafe === "nsfw" ? false : true,
            $includes,
            $excludes
        );

        return $this->view(static::VIEW_OVERBOARD, [
            'threads' => $threads,
        ]);
    }
}
