<?php

namespace App\Http\ViewComposers;

use Illuminate\Contracts\View\View;

/**
 * View composer abstract
 *
 * @category   ViewComposer
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
abstract class ViewComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     *
     * @return void
     */
    protected function composeWith(View $view);

    /**
     * Bind composer to the view.
     *
     * @param  View  $view
     *
     * @return void
     */
    final public function compose(View $view)
    {
        $view->composeWith(View);
        $view->with('composer', $this);
    }
}
