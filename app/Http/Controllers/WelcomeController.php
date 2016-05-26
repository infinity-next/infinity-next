<?php

namespace App\Http\Controllers;

use App\Board;
use App\Post;
use App\Http\Controllers\Board\BoardStats;
use Illuminate\Support\Facades\View;

/**
 * Index page.
 *
 * @package    InfinityNext
 * @category   Controller
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 * @since      0.5.1
 */
class WelcomeController extends Controller
{
    use BoardStats;

    /**
     * View file for the main index page container.
     *
     * @var string
     */
    const VIEW_INDEX = "index";

    /**
     * Show the application welcome screen to the user.
     *
     * @return Response
     */
    public function getIndex()
    {
        if ($featured = Post::getPostFeatured())
        {
            $featured->setRelation('replies', []);
        }

        $featured_boards = Board::getFeatured(5);

        return $this->view(static::VIEW_INDEX, [
            'featured'        => $featured,
            'featured_boards' => $featured_boards,
            'stats'           => $this->boardStats(),
        ]);
    }

}
