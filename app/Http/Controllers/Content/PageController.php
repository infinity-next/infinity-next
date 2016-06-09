<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Payment;
use App\Page;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;

/**
 * Distributes static content.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.5.1
 */
class PageController extends Controller
{
    const VIEW_PAGE = "board.page";

    /**
     * Show a single static document.
     *
     * @return Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        if (!$page->exists) {
            return abort(404);
        }

        return $this->view(static::VIEW_PAGE, [
            'page' => $page,
        ]);
    }
}
