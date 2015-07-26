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
		
		$relative = "/\s?>>(?P<board_id>\d+)\s?/";
		$global   = "/\s?>>>\/(?P<board_uri>" . Board::URI_PATTERN_INNER . ")\/(?P<board_id>\d+)?\s?/";
		
		foreach ($lines as $line)
		{
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
			$posts  = Post::where(function($query) use ($postCites)
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
	 * Returns a formatted post.
	 *
	 * @param  \App\Post $post
	 * @return String (HTML, Formatted)
	 */
	public function formatPost(Post $post)
	{
		$this->post = $post;
		
		return Markdown::setBreaksEnabled(true)
			->setMarkupEscaped(true)
			//->setUrlsLinked(false)
			->parse( (string) $post->body );
		
		
		return $this->formatContent( (string) $post->body);
	}
	
	/**
	 * Santizes user input for a single line.
	 *
	 * @param  string $content
	 * @return string
	 */
	protected function encodeContent($content)
	{
		return htmlentities( (string) $content );
	}
	
	/**
	 * Parses an entire block of text.
	 *
	 * @param  string $content
	 * @return string
	 */
	protected function formatContent($content)
	{
		$html    = "";
		$content = $this->encodeContent($content);
		
		if ($content != "")
		{
			$lines   = explode("\n", $content);
			
			foreach ($lines as $line)
			{
				$html .= $this->formatLine($line);
			}
		}
		
		return $html;
	}
	
	/**
	 * Parses a single line.
	 *
	 * @param  string $content
	 * @return string
	 */
	protected function formatLine($line)
	{
		$html       = "";
		$cssClasses = ["line"];
		
		$this->parseQuotes($line, $cssClasses);
		$this->parseCites($line, $cssClasses);
		
		$html .= "<p class=\"" . implode($cssClasses, " ") . "\">";
		$html .= $line;
		$html .= "</p>";
		
		return $html;
	}
	
	/**
	 * Takes parsed citations and converts cites to hyperlinks.
	 *
	 * @param  string &$line
	 * @param  array &$cssClasses
	 * @return void
	 */
	protected function parseCites(&$line, array &$cssClasses)
	{
		$words = explode(" ", $line);
		
		foreach ($this->post->cites as $cite)
		{
			$replacements = [];
			
			if ($cite->cite_board_id)
			{
				$replacements["/^&gt;&gt;&gt;\/{$cite->cite_board_uri}\/{$cite->cite_board_id}\r?$/"] = $this->buildCiteLink($cite, true,  true);
				$replacements["/^&gt;&gt;{$cite->cite_board_id}\r?$/"] = $this->buildCiteLink($cite, false, true);
			}
			else
			{
				$replacements["/^&gt;&gt;&gt;\/{$cite->cite_board_uri}\/\r?$/"] = $this->buildCiteLink($cite, false, false);
			}
			
			foreach ($words as &$word)
			{
				foreach ($replacements as $pattern => $replacement)
				{
					if (preg_match($pattern, $word))
					{
						$word = $replacement;
						break;
					}
				}
			}
		}
		
		$line = implode(" ", $words);
	}
	
	/**
	 * Builds an anchor tag with supplied models.
	 *
	 * @param  \App\PostCite $cite
	 * @param  boolean $remote
	 * @param  boolean $post
	 * @return string
	 */
	protected function buildCiteLink(PostCite $cite, $remote = false, $post = false)
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
					return "<a href=\"{$url}\" " .
						"class=\"cite cite-post cite-remote\" " .
						"data-board_uri=\"{$cite->cite_board_uri}\" " .
						"data-board_id=\"{$cite->cite_board_id}\" " .
						">" .
							"&gt;&gt;&gt;/{$cite->cite_board_uri}/{$cite->cite_board_id}" .
						"</a>";
				}
				else
				{
					return "<a href=\"{$url}\" " .
						"class=\"cite cite-post cite-local\" " .
						"data-board_uri=\"{$cite->cite_board_uri}\" " .
						"data-board_id=\"{$cite->cite_board_id}\" " .
						">" .
							"&gt;&gt;{$cite->cite_board_id}" .
						"</a>";
				}
			}
		}
		else
		{
			$url = "/{$cite->cite_board_uri}/";
			
			return "<a href=\"{$url}\" " .
				"class=\"cite cite-board cite-remote\" " .
				"data-board_uri=\"{$cite->cite_board_uri}\" " .
				">" .
					"&gt;&gt;&gt;/{$cite->cite_board_uri}/" .
				"</a>";
		}
	}
	
	/**
	 * Parses quotes in a post.
	 *
	 * @param  string &$line
	 * @param  array &$cssClasses
	 * @return void
	 */
	protected function parseQuotes(&$line, array &$cssClasses)
	{
		if (strpos($line, $this->encodeContent(">")) === 0)
		{
			$cssClasses[] = "quote";
		}
	}
	
}
