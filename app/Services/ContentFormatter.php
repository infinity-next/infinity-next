<?php namespace App\Services;

use App\Post;

class ContentFormatter {
	
	/**
	 * Returns a formatted post.
	 *
	 * @param  \App\Post Post
	 * @return String (HTML, Formatted)
	 */
	public function formatPost(Post $post)
	{
		return $this->formatContent( (string) $post->body);
	}
	
	protected function encodeContent($content)
	{
		return htmlentities( (string) $content );
	}
	
	protected function formatContent($content)
	{
		$html    = "";
		$content = $this->encodeContent($content);
		$lines   = explode("\n", $content);
		
		foreach ($lines as $line)
		{
			$html .= $this->formatLine($line);
		}
		
		return $html;
	}
	
	protected function formatLine($line)
	{
		$html       = "";
		$cssClasses = ["line"];
		
		if (strpos($line, $this->encodeContent(">")) === 0)
		{
			$cssClasses[] = "quote";
		}
		
		$html .= "<p class=\"" . implode($cssClasses, " ") . "\">";
		$html .= $line;
		$html .= "</p>";
		
		return $html;
	}
}
