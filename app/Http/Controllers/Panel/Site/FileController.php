<?php

namespace App\Http\Controllers\Panel\Site;

use App\FileStorage;
use App\Http\Controllers\Panel\PanelController;
use App\Http\SendsFilesTrait as SendsFiles;

/**
 * File management tools for the site.
 *
 * @category   Controller
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
class FileController extends PanelController
{
    use SendsFiles;

    const VIEW_DASHBOARD = 'panel.site.files';

    /**
     * View path for the secondary (sidebar) navigation.
     *
     * @var string
     */
    public static $navSecondary = 'nav.panel.site';

    /**
     * Show all recent files.
     */
    public function index()
    {
        $this->authorize('admin-config');

        $files = FileStorage::orderBy('last_uploaded_at', 'desc')
            ->where('last_uploaded_at', '>', now()->subDay())
            ->get();

        return $this->makeView(static::VIEW_DASHBOARD, [
            'files' => $files,
        ]);
    }

    /**
     * Sends the attachment data
     */
    public function send($hash)
    {
        $this->authorize('admin-config');

        return $this->sendFile($hash);
    }

    /**
     * Shows information about a file.
     */
}
