<?php

namespace App\Support;

use App\Services\ContentFormatter;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class API for syntactical markup formatting.
 *
 * @category   Support
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
trait Formattable
{
    /**
     * Associative array of formatting fields.
     *
     * @var array
     */
    protected $formattable = [
        'input_text' => 'body',
        'parsed_html' => 'body_parsed',
        'parsed_at' => 'body_parsed_at',
    ];

    /**
     * Tests if this formattable class can cite posts and boards.
     *
     * @return bool
     */
    public function canCite()
    {
        $method = 'cite';
        return is_callable([$this, $method])
            && $this->{$method}() instanceof BelongsToMany;
    }

    /**
     * Tests if this formattable class can throw dice.
     *
     * @return bool
     */
    public function canDice()
    {
        $method = 'dice';
        return method_exists($this, 'dice') && is_callable([$this, $method])
            && $this->{$method}() instanceof BelongsToMany;
    }

    /**
     * Get content as a string of HTML.
     *
     * @return string
     */
    public function getFormatted($skipCache = false)
    {
        $parsed_html = $this->{$this->formattable['parsed_html']};
        $parsed_at = $this->{$this->formattable['parsed_at']};

        if (!$skipCache && !is_null($parsed_html)) {
            return $parsed_html;
        }

        $ContentFormatter = new ContentFormatter();
        $this->{$this->formattable['parsed_html']} = $parsed_html = $ContentFormatter->formatPage($this, $this->formattable['input_text']);
        $this->{$this->formattable['parsed_at']} = $parsed_at = $this->freshTimestamp();

        if (!mb_check_encoding($parsed_html, 'UTF-8')) {
            return '<tt style="color:red;">Invalid encoding. This should never happen!</tt>';
        }

        // This is a partial update.
        // We don't want to force the model to save early.
        static::where([$this->primaryKey => $this->{$this->primaryKey}])->update([
            $this->formattable['parsed_html'] => $parsed_html,
            $this->formattable['parsed_at'] => $parsed_at,
        ]);

        return $parsed_html;
    }
}
