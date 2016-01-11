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
		return Response::make(View::make(static::VIEW_GLOBAL_NAV))->setTtl(60);
	}
	
	public function getRecentImages()
	{
		return Response::make(View::make(static::VIEW_RECENT_IMAGES))->setTtl(60);
	}
	
	public function getRecentPosts()
	{
		return Response::make(View::make(static::VIEW_RECENT_POSTS))->setTtl(60);
	}
	
}
