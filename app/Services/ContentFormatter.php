<?php namespace App\Services;

use App\Board;
use App\Post;
use App\PostCite;

use App\Contracts\Support\Formattable as FormattableContract;
use Illuminate\Database\Eloquent\Collection;

use DB;
use Markdown;

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
class ContentFormatter {

    /**
     * Is set to true if non-citation text is detected.
     *
     * @var bool
     */
    protected $hasContent = false;

    /**
     * The formattable being parsed.
     *
     * @var \App\formattable $formattable
     */
    protected $formattable;

    /**
     * Markdown options
     *
     * @var array
     */
    protected $options;

    /**
     * Censor terms (xxx => zzz)
     *
     * @var array
     */
    protected $wordfilters = [];

    /**
     * Builds an array of attributes for the Parsedown engine.
     *
     * @param  \App\PostCite  $cite
     * @param  boolean  $remote
     * @param  boolean  $post
     * @return string
     */
    protected function buildCiteAttributes(PostCite $cite, $remote = false, $post = false)
    {
        if ($post)
        {
            if ($cite->cite)
            {
                if ($cite->cite->reply_to)
                {
                    $url = "/{$cite->cite_board_uri}/thread/{$cite->cite->reply_to_board_id}#{$cite->cite_board_id}";
                }
                else
                {
                    $url = "/{$cite->cite_board_uri}/thread/{$cite->cite_board_id}#{$cite->cite_board_id}";
                }

                if ($remote)
                {
                    return [
                        'href'           => $url,
                        'class'          => "cite cite-post cite-remote",
                        'data-board_uri' => $cite->cite_board_uri,
                        'data-board_id'  => $cite->cite_board_id,
                    ];
                }
                else
                {
                    return [
                        'href'           => $url,
                        'class'          => "cite cite-post cite-local",
                        'data-board_uri' => $cite->cite_board_uri,
                        'data-board_id'  => $cite->cite_board_id,
                        'data-instant',
                    ];
                }
            }
        }
        else
        {
            $url = "/{$cite->cite_board_uri}/";

            return [
                'href'           => $url,
                'class'          => "cite cite-board cite-remote",
                'data-board-uri' => $cite->cite_board_uri,
            ];
        }
    }

    /**
     * Returns a formatted static page.
     *
     * @param  \App\Contacts\Support\Formattable  $formattable
     * @return string  (HTML, Formatted)
     */
    public function formatPage(FormattableContract $formattable, $formatKey)
    {
        $this->formattable = $formattable;

        $this->options = [
            'general' => [
                'keepLineBreaks' => true,
                'parseHTML'      => false,
                'parseURL'       => true,
            ],

            // 'disable' => [
            //     "Image",
            //     "Link",
            // ],

            'enable' => [
                "Spoiler",
                "Underline",
            ],

            'markup' => [
                'quote'   => [
                    'keepSigns' => true,
                ],
            ],
        ];

        return $this->formatContent($this->formattable->{$formatKey});
    }

    /**
     * Returns a formatted formattable.
     *
     * @param  \App\Post|string  $post
     * @param  int|null  $splice  Optional. First number of characters to parse instead of entire formattable. Defaults to null.
     * @return string  (HTML, Formatted)
     */
    public function formatPost($post, $splice = null)
    {
        if ($post instanceof FormattableContract)
        {
            $this->formattable = $post;
            $body = (string) $post->body;
            $this->wordfilters = $post->board->getWordfilters();
        }
        else
        {
            $body = (string) $post;
        }

        if (!is_null($splice))
        {
            $body = mb_substr($body, 0, (int) $splice);
        }

        $this->options = [
            'general' => [
                'keepLineBreaks' => true,
                'parseHTML'      => false,
                'parseURL'       => true,
            ],

            'disable' => [
                "Image",
                "Link",
            ],

            'enable' => [
                "Spoiler",
                "Underline",
            ],

            'markup' => [
                'quote'   => [
                    'keepSigns' => true,
                ],
            ],
        ];

        return $this->formatContent($body);
    }

    /**
     * Returns a tripcode from a password.
     * Note that this is a public, easily breakable algorithm, and is therefore insecure.
     * However, it is retained because of its heavy use on anonymous websites from 2ch to 4chan.
     *
     * @param  string  $trip
     * @return string  (Tripcode)
     */
    public static function formatInsecureTripcode($trip)
    {
        $trip = mb_convert_encoding($trip, 'Shift_JIS', 'UTF-8');
        $salt = substr($trip . 'H..', 1, 2);
        $salt = preg_replace('/[^.-z]/', '.', $salt);
        $salt = strtr($salt, ':;<=>?@[\]^_`', 'ABCDEFGabcdef');
        $trip = substr(crypt($trip, $salt), -10);
        return $trip;
    }

    /**
     * Censors content.
     *
     * @return string
     */
    protected function formatCensors($content)
    {
        foreach ($this->wordfilters as $find => $replace)
        {
            // Matches |bad| but not |<span class="censored">bad</span>|.
            $pattern = "/<span class=\\\"censored.*?<\\/span>|(?P<match>\\b{$find}\\b)/";

            try
            {
                $newContent = preg_replace_callback($pattern, function ($matches) use ($replace) {
                    if (isset($matches['match']))
                    {
                        $randBool = mt_rand(0, 1) ? "odd" : "even";
                        $randTens = mt_rand(1, 10);

                        $censoredWord = strtolower(preg_replace("/[^a-zA-Z\d]/", "", $replace));
                        $censoredWord = strlen($censoredWord) ? "word-{$censoredWord}" : "";

                        return "<span class=\"censored {$censoredWord} rand-{$randBool} rand-{$randTens}\">{$replace}</span>";
                    }

                    return $matches[0];
                }, $content);

                $content = $newContent;
            }
            // RegEx error
            catch (\ErrorException $e)
            {
                // RegEx is malformed.
            }
        }

        return $content;
    }

    /**
     * Parses an entire block of text.
     *
     * @param  string  $content
     * @return string
     */
    protected function formatContent($content)
    {
        $content = $this->formatMarkdown($content);
        $content = $this->formatCensors($content);

        // Removes any citations.
        $citelessContent  = preg_replace("/(<a(?: \w+=\"[^\"].+\")* class=\"cite(?:[^\"].*)\"(?: \w+=\"[^\"]+\")*>(?:[^<].*)<\/a>)/im", "", $content);
        // Removes any other tags.
        $citelessContent  = preg_replace("/(<(?:[^\>]*)>)/", "", $citelessContent);
        // Removes all whitespace.
        $citelessContent  = preg_replace("/\s/", "", $citelessContent);
        // If anything is left, we have content.
        $this->hasContent = (strlen($citelessContent) > 0);

        return $content;
    }

    /**
     * Santizes user input for a single line.
     *
     * @param  string  $content
     * @return string
     */
    protected function formatMarkdown($content)
    {
        $content = Markdown::config($this->options)
            ->addInlineType('>', 'Cite')
            ->addInlineType('&', 'Cite')
            ->extendInline('Cite', $this->getCiteParser())
            ->parse($content);

        return $content;
    }

    /**
     * Returns a formatted report rule text.
     *
     * @param  string  $text
     * @return string  (HTML, Formatted)
     */
    public function formatReportText($text)
    {
        $this->options = [
            'general' => [
                'keepLineBreaks' => true,
                'parseHTML'      => false,
                'parseURL'       => true,
            ],

            'disable' => [
                "Image",
                "Link",
            ],

            'enable' => [
                "Spoiler",
            ],

            'markup' => [
                'quote'   => [
                    'keepSigns' => true,
                ],
            ],
        ];

        return $this->formatContent($text);
    }

    /**
     * Gets the hasContent property.
     *
     * @return bool
     */
    public function hasContent()
    {
        return $this->hasContent;
    }

    /**
     * Provides a closure for the Eightdown API that adds citations inline.
     *
     * @return Closure
     */
    protected function getCiteParser()
    {
        $parser = $this;

        return function($Excerpt) use ($parser)
        {
            $Element = [
                'name'       => 'a',
                'handler'    => 'line',
                'text'       => null,
                'attributes' => [
                    'href'      => null,
                    'title'     => null,
                ],
            ];

            $extent = 0;

            $remainder = $Excerpt['text'];

            if (preg_match('/^((?:(&gt;)|>)>>\/(?P<board_uri>' . Board::URI_PATTERN_INNER . ')\/(?P<board_id>\d+)?)/usi', $Excerpt['text'], $matches))
            {
                $Element['text'] = str_replace("&gt;", ">", $matches[0]);

                $extent += strlen($matches[0]);
            }
            else if (preg_match('/((?:(&gt;)|>)>(?P<board_id>\d+))(?!>)/us', $Excerpt['text'], $matches))
            {
                $Element['text'] = str_replace("&gt;", ">", $matches[0]);

                $extent += strlen($matches[0]);
            }
            else
            {
                return;
            }

            $replaced = false;

            if (isset($parser->formattable) && $parser->formattable instanceof FormattableContract)
            {
                foreach ($parser->formattable->cites as $cite)
                {
                    $replacements = [];

                    if ($cite->cite_board_id)
                    {
                        $replacements["/^>>>\/{$cite->cite_board_uri}\/{$cite->cite_board_id}\r?/"] = $parser->buildCiteAttributes($cite, true,  true);
                        $replacements["/^>>{$cite->cite_board_id}\r?/"] = $parser->buildCiteAttributes($cite, false, true);
                    }
                    else
                    {
                        $replacements["/^>>>\/{$cite->cite_board_uri}\/\r?/"] = $parser->buildCiteAttributes($cite, false, false);
                    }

                    foreach ($replacements as $pattern => $replacement)
                    {
                        if (preg_match($pattern, $Element['text']))
                        {
                            $Element['attributes'] = $replacement;
                            $replaced = true;
                            break 2;
                        }
                    }
                }
            }

            if ($replaced)
            {
                $Element['text']       = str_replace(">", "&gt;", $Element['text']);

                return [
                    'extent'   => $extent,
                    'element'  => $Element,
                ];
            }
        };
    }

    /**
     * Returns a collection of formattables as cited in a formattable's text body.
     *
     * @param  \App\Contracts\Support\Formattable $formattable
     * @return Collection
     */
    public static function getCites(FormattableContract $formattable)
    {
        $postCites  = [];
        $boardCites = [];
        $lines = explode("\n", $formattable->body);

        $relative = "/\s?&gt;&gt;(?P<board_id>\d+)\s?/";
        $global   = "/\s?&gt;&gt;&gt;\/(?P<board_uri>" . Board::URI_PATTERN_INNER . ")\/(?P<board_id>\d+)?\s?/";

        foreach ($lines as $line)
        {
            $line = str_replace(">", "&gt;", $line);

            preg_match_all($relative, $line, $relativeMatch);
            preg_match_all($global, $line, $globalMatch);

            if (isset($relativeMatch['board_id']))
            {
                foreach($relativeMatch['board_id'] as $matchIndex => $matchBoardId)
                {
                    $postCites[] = [
                        'board_uri' => $formattable->board_uri,
                        'board_id'  => $matchBoardId,
                    ];
                }
            }

            if (isset($globalMatch['board_uri']))
            {
                foreach($globalMatch['board_uri'] as $matchIndex => $matchBoardUri)
                {
                    $matchBoardId = $globalMatch['board_id'][$matchIndex];

                    if ($matchBoardId != "")
                    {
                        $postCites[] = [
                            'board_uri' => $matchBoardUri,
                            'board_id'  => $matchBoardId,
                        ];
                    }
                    else
                    {
                        $boardCites[] = $matchBoardUri;
                    }
                }
            }
        }

        // Fetch all the boards and relevant content.
        if (count($boardCites))
        {
            $boards = Board::whereIn('board_uri', $boardCites)->get();
        }
        else
        {
            $boards = new Collection;
        }

        if (count($postCites))
        {
            $posts = Post::where(function($query) use ($postCites)
            {
                foreach ($postCites as $postCite)
                {
                    $query->orWhere(function($query) use ($postCite)
                    {
                        $query->where('board_uri', $postCite['board_uri'])
                            ->where('board_id', $postCite['board_id']);
                    });
                }
            })->get();
        }
        else
        {
            $posts = new Collection;
        }

        return [
            'boards' => $boards,
            'posts'  => $posts,
        ];
    }

}
