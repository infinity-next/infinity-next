<?php namespace App\Http\Controllers\Internal;

use App\Http\Controllers\Internal\InternalController;
use Response;
use View;

class SiteController extends InternalController {
	
	const VIEW_GLOBAL_NAV    = "nav.gnav";
	const VIEW_RECENT_IMAGES = "content.index.sections.recent_images";
	const VIEW_RECENT_POSTS  = "content.index.sections.recent_posts";
	
	public function getGlobalNavigation()
	{
		$partial = View::make(static::VIEW_GLOBAL_NAV);
		$partial .= "<!-- ESI " . date('r') . "-->";
		
		return Response::make($partial)
			->setTtl(60);
	}
	
	public function getRecentImages()
	{
		$partial = View::make(static::VIEW_RECENT_IMAGES);
		$partial .= "<!-- ESI " . date('r') . "-->";
		
		return Response::make($partial)
			->setTtl(60);
	}
	
	public function getRecentPosts()
	{
		$partial = View::make(static::VIEW_RECENT_POSTS);
		$partial .= "<!-- ESI " . date('r') . "-->";
		
		return Response::make($partial)
			->setTtl(60);
	}
	
}
