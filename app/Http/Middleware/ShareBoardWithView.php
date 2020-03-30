<?php

namespace App\Http\Middleware;

use App\Board;
use Closure;

/**
 * Adds the board global to the view.
 *
 * @category   Middleware
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class ShareBoardWithView
{
    public function handle($request, Closure $next)
    {
        view()->share([
            'board' => app(Board::class),
        ]);

        $request->route()->forgetParameter('board');

        return $next($request);
    }
}
