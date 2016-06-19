<?php

namespace App;

use App\Contracts\Support\Formattable as FormattableContract;
use App\Support\Formattable;
use Illuminate\Contracts\Support\Htmlable as HtmlableContarct;
use Illuminate\Database\Eloquent\Model;

/**
 * Model representing static page content for boards and the global site.
 *
 * @package    InfinityNext
 * @category   Model
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 * @since      0.6.0
 */
class Page extends Model implements HtmlableContarct, FormattableContract
{
    use Formattable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pages';

    /**
     * The primary key that is used by ::get()
     *
     * @var string
     */
    protected $primaryKey = 'page_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_at',
        'updated_at',
        'deleted_at',
        'board_uri',
        'name',
        'title',
        'body',
        'body_parsed',
        'body_parsed_at',
    ];

    /**
     * Attributes which are automatically sent through a Carbon instance on load.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'body_parsed_at',
    ];

    /**
     * Board relationship.
     *
     * @return Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function board()
    {
        return $this->belongsTo('\App\Board', 'board_uri');
    }

    /**
     * Human-readable display name.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->title . ".html";
    }

    /**
     * Htmlable API for rendered page
     *
     * @return string
     */
    public function toHtml()
    {
        return view('content.panel.page.partial', [
            'board'   => $this->board,
            'page'    => $this,
            'content' => $this->getFormatted(),
        ]);
    }
}
