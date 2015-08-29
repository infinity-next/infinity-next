<?php namespace App\Services;

use App\Board;
use App\Post;
use App\PostCite;

use Illuminate\Database\Eloquent\Collection;

use DB;
use Markdown;

class ContentFormatter {
	
	/**
	 * The post being parsed.
	 *
	 * @var \App\Post $post
	 */
	protected $post;
	
	/**
	 * Markdown options
	 *
	 * @var array
	 */
	protected $options;
	
	
	/**
	 * Returns a formatted post.
	 *
	 * @param  \App\Post $post
	 * @return String (HTML, Formatted)
	 */
	public function formatPost(Post $post)
	{
		$this->post	= $post;
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
		
		return $this->formatContent( (string) $post->body);
	}
	
	/**
	 * Returns a formatted sidebar.
	 *
	 * @param  string  $text
	 * @return string (HTML, Formatted)
	 */
	public function formatSidebar($text)
	{
		$this->options = [
			'general' => [
				'keepLineBreaks' => true,
				'parseHTML'	  => true,
				'parseURL'	   => true,
			],
		];
		
		return $this->formatContent($text);
	}
	
	/**
	 * Returns a formatted report rule text.
	 *
	 * @param  string  $text
	 * @return string (HTML, Formatted)
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
	 * Parses an entire block of text.
	 *
	 * @param  string $content
	 * @return string
	 */
	protected function formatContent($content)
	{
		$html = "";
		
		$html = $this->formatMarkdown($content);
		
		return $html;
	}
	
	/**
	 * Santizes user input for a single line.
	 *
	 * @param  string $content
	 * @return string
	 */
	protected function formatMarkdown($content)
	{
		$post   = $this->post;
		
		$content = Markdown::config($this->options)
			->extendBlockComplete('Quote', $this->getQuoteParser())
			->addInlineType('>', 'Cite')
			->addInlineType('&', 'Cite')
			->extendInline('Cite', $this->getCiteParser())
			->parse($content);
		
		return $content;
	}
	
	/**
	 * Provides a closure for the Eightdown API to deal with spoilers after a quote block is complete.
	 *
	 * @return Closure
	 */
	protected function getQuoteParser()
	{
		$parser = $this;
		
		return function($Block) use ($parser)
		{
			$spoiler = null;
			
			foreach ($Block['element']['text'] as &$text)
			{
				// $text = str_replace(">", "&gt;", $text);
				
				$spoiler = (($spoiler === true || is_null($spoiler)) && preg_match('/^&gt;![ ]?(.*)/', $text, $matches));
				
			}
			
			if ($spoiler === true)
			{
				$Block['element']['attributes']['class'] = "spoiler";
				
				foreach ($Block['element']['text'] as &$text)
				{
					$text = preg_replace('/^&gt;!/', "", $text, 1);
				}
			}
			
			return $Block;
		};
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
			
			foreach ($parser->post->cites as $cite)
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
	 * Returns a collection of posts as cited in a post's text body.
	 *
	 * @param  \App\Post $post
	 * @return Collection
	 */
	public static function getCites(Post $post)
	{
		$postCites  = [];
		$boardCites = [];
		$lines = explode("\n", $post->body);
		
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
						'board_uri' => $post->board_uri,
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
	
	/**
	 * Builds an array of attributes for the Parsedown engine.
	 *
	 * @param  \App\PostCite $cite
	 * @param  boolean $remote
	 * @param  boolean $post
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
					$url = url("/{$cite->cite_board_uri}/thread/{$cite->cite->reply_to_board_id}#{$cite->cite_board_id}");
				}
				else
				{
					$url = url("/{$cite->cite_board_uri}/thread/{$cite->cite_board_id}#{$cite->cite_board_id}");
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
			$url = url("/{$cite->cite_board_uri}/");
			
			return [
				'href'           => $url,
				'class'          => "cite cite-board cite-remote",
				'data-board-uri' => $cite->cite_board_uri,
			];
		}
	}
	
}
