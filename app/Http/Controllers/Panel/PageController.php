<?php

namespace App\Http\Controllers\Panel;

use App\Board;
use App\Page;

use Illuminate\Http\Request;

use Carbon\Carbon;
use DB;
use Input;
use Session;
use Validator;

/**
 * This is the board config controller, available with appropriate permissions.
 * Its job is to load config panels and to validate and save the changes.
 *
 * @package    InfinityNext
 * @category   Controller
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 * @since      0.6.0
 */
class PageController extends PanelController
{
    const VIEW_INDEX  = "panel.page.index";
    const VIEW_CREATE = "panel.page.edit";
    const VIEW_SHOW   = "panel.page.show";
    const VIEW_EDIT   = "panel.page.edit";
    const VIEW_DELETE = "panel.page.delete";

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = "nav.panel.board";

    /**
     * View path for the tertiary (inner) navigation.
     *
     * @var string
     */
    public static $navTertiary = "nav.panel.board.settings";

    /**
     * Shares variables with the views.
     *
     * @param  \App\Board  $board  Current board.
     * @return void
     */
    public function boot()
    {
        if (!$this->board)
        {
            $this::$navSecondary = "nav.panel.site";
            $this::$navTertiary = null;
        }

        view()->share([
            'tab'   => "pages",
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pages = Page::where([
            'board_uri' => $this->board ? $this->board->board_uri : null,
        ])->get();

        return $this->view(static::VIEW_INDEX, [
            'pages' => $pages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->view(static::VIEW_CREATE, [
            'page' => false,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $board = $this->board;
        $request->replace([
            'name'  => $request->get('name'),
            'title' => str_slug($request->get('name')),
            'body'  => $request->get('body'),
        ]);

        $this->validate($request, [
            'name'  => 'required|unique:pages|max:255',
            'title' => 'required|unique:pages|max:255',
            'body'  => 'required',
        ]);

        $page = new Page([
            'name' => $request->get('name'),
            'title' => $request->get('title'),
            'body' => $request->get('body'),
            'board_uri' => $board ? $board->board_uri : null,
        ]);

        $page->save();

        return $this->view(static::VIEW_SHOW, [
            'page' => $page,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function show(Page $page)
    {
        return $this->view(static::VIEW_SHOW, [
            'page' => $page,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function edit(Page $page)
    {
        return $this->view(static::VIEW_EDIT, [
            'page' => $page,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Page $page)
    {
        $request->replace([
            'name'  => $request->get('name'),
            'title' => str_slug($request->get('name')),
            'body'  => $request->get('body'),
        ]);

        $this->validate($request, [
            'name'  => [
                "required",
                "unique:pages,name,{$page->page_id},page_id",
                "max:255",
            ],
            'title' => [
                "required",
                "unique:pages,title,{$page->page_id},page_id",
                "max:255",
            ],
            'body'  => 'required',
        ]);

        $page->fill([
            'name' => $request->get('name'),
            'title' => $request->get('title'),
            'body' => $request->get('body'),
        ]);


        Session::flash('success', trans('config.success'));
        $page->save();

        return $this->edit($page);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function delete(Page $page)
    {
        return $this->view(static::VIEW_DELETE, [
            'page' => $page,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Page  $page
     * @return \Illuminate\Http\Response
     */
    public function destroy($page)
    {
        $page->delete();

        return redirect()->route(
            'panel.' .
            ($board->exists ? 'board.' : null) .
            'page.' .
            'index', [
                'board' => $this->board ? $board : null,
            ]
        );
    }
}
