<?php

namespace App\Http\Controllers\API\Content;

use App\Contracts\ApiController as ApiContract;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\Content\MultiboardController as ParentController;
use Request;
use Input;

/**
 * API controller for the Multiboard.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class MultiboardController extends ParentController implements ApiContract
{
    use ApiController;

    public function getOverboard($worksafe = null, $boards = null, $catalog = false)
    {
        $updatedSince = Request::get('updatedSince', null);

        if (!is_null($updatedSince) && !is_numeric($updatedSince)) {
            return abort(400);
        }

        $threads = $this->prepareThreads($worksafe, $boards, $catalog, $updatedSince);
        $threads->each(function($thread) use ($catalog)
        {
            $thread->renderCatalog = $catalog;
            $thread->renderMultiboard = true;
            $thread->renderPartial = false;
            $thread->setAppendHtml(true);
        });

        return $threads;
    }
}
