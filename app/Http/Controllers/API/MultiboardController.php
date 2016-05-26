<?php

namespace App\Http\Controllers\API;

use App\Contracts\ApiController as ApiContract;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\MultiboardController as ParentController;
use App\Post;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

use Cache;
use Input;
use Request;
use Validator;

/**
 * API controller for the Multiboard.
 *
 * @package    InfinityNext
 * @category   Controller
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 * @since      0.6.0
 */
class MultiboardController extends ParentController implements ApiContract
{
    use ApiController;

    public function getOverboard()
    {
        return $this->getThreads(max(1, Input::get('page', 1)));
    }

}
