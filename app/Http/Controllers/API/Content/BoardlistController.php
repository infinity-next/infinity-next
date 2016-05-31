<?php

namespace App\Http\Controllers\API\Content;

use App\Board;
use App\Contracts\ApiController as ApiContract;
use App\Http\Controllers\API\ApiController;
use App\Http\Controllers\Content\BoardlistController as ParentController;
use Input;
use Request;

class BoardlistController extends ParentController implements ApiContract
{
    use ApiController;

    /**
     * Show board list to the user, either rendering the full blade template or just the json..
     *
     * @return Response|JSON
     */
    public function getIndex()
    {
        return $this->boardListJson();
    }

    /**
     * Returns basic board details.
     *
     * @return Response
     */
    public function getDetails(Request $request)
    {
        $boardUris = Input::get('boards', []);
        $boards = Board::select('board_uri', 'title')
            ->whereIn('board_uri', $boardUris)
            ->orderBy('board_uri', 'desc')
            ->take(20)
            ->get();

        return $boards;
    }
}
