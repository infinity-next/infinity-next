<?php

namespace App\Http\ViewComposers\Board;

use App\Http\Board\BoardComposer;

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
class CatalogComposer extends BoardComposer
{
    /**
     * Returns true if this is a catalog view.
     *
     * A catalog view will only show the original post of a thread.
     *
     * @return bool
     */
    public function isCatalog()
    {
        return true;
    }
}
