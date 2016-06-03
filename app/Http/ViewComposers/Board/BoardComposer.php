<?php

namespace App\Http\ViewComposers\Board;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\ViewComposer;

/**
 * Board composer
 *
 * Shared between rendering modes, like threaded and catalog.
 *
 * @category   ViewComposer
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
abstract class BoardComposer extends ViewComposer
{
    /**
     * Boards used for this render.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $boards = collect();

    /**
     * Current page number.
     *
     * Zero indexed.
     *
     * @var integer
     */
    protected $page = 0;

    /**
     * Number of pages.
     *
     * @var integer
     */
    protected $pageCount;

    /**
     * Number of threads per page.
     *
     * @var integer
     */
    protected $pageSize;

    /**
     * Threads used for this render.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $threads = collect();

    /**
     * Binds data to the view.
     *
     * @return void
     */
    public function composeWith(View $view)
    {
        $view->with('boards', $this->getBoards());
        $view->with('threads', $this->getThreads());
     }

    /**
     * Counts our board collection.
     *
     * @return integer
     */
    public function countBoards()
    {
        return $this->boards->count();
    }

    /**
     * Counts the number of pages for this view.
     *
     * @return integer
     */
    public function countPages()
    {
        return $this->pageCount;
    }

    /**
     * Counts our thread collection.
     *
     * @return integer
     */
    public function countThreads()
    {
        return $this->threads->count();
    }

    /**
     * Gets all boards used for this render.
     *
     * @param  integer  $index  nth board to grab. Defaults 0 for first.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBoard($index = 0)
    {
        return $this->boards->get($index);
    }

    /**
     * Gets all boards used for this render.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getBoards()
    {
        return $this->boards;
    }

    /**
     * Returns a paginator support object for this view.
     *
     * @reutrn \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginator()
    {
        return new LengthAwarePaginator(
            $this->getThreads(),
            $this->countThreads(),
            $this->countPages(),
            LengthAwarePaginator::resolveCurrentPage(),
            [
                'path' => LengthAwarePaginator::resolveCurrentPath()
            ]
        );
    }

    /**
     * Gets all threads used for this render.
     *
     * @param  integer  $index  nth thread to grab. Defaults 0 for first.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getThread($index = 0)
    {
        return $this->threads->get($index);
    }

    /**
     * Gets all threads used for this render.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getThreads()
    {
        return $this->threads;
    }

    /**
     * Returns true if we have any boards.
     *
     * @return bool
     */
    public function hasBoards()
    {
        return $this->countBoards() > 0;
    }

    /**
     * Returns true if this view has pages.
     *
     * @return bool
     */
    public function hasPages()
    {
        return $this->pageCount > 0;
    }

    /**
     * Returns true if we have any threads.
     *
     * @return bool
     */
    public function hasThreads()
    {
        return $this->countThreads() > 0;
    }

    /**
     * Returns true if this is a catalog view.
     *
     * A catalog view will only show the original post of a thread.
     *
     * @return bool
     */
    public function isCatalog()
    {
        return false;
    }

    /**
     * Returns true if we have more than one board.
     *
     * @return bool
     */
    public function isMultiboard();

    /**
     * Returns true if this is a thread view.
     *
     * A thread is a single original post and many replies, that may or may
     * not be paginated or split into subsections.
     *
     * @return bool
     */
    public function isThread()
    {
        return false;
    }

    /**
     * Returns true if this is a threaded view.
     *
     * A threaded view shows many original posts and a variable number of the
     * latest replies, in accordance to special rules.
     *
     * @return bool
     */
    public function isThreaded()
    {
        return false;
    }
}
