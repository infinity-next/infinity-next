<?php

/**
 * ESI (edge-side include) routes for Varnish and other edge caches.
 *
 * @category   Routes
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */
Route::group([
    'namespace' => 'Internal',
    'prefix' => '.internal',
], function () {
    Route::get('site/global-nav', 'SiteController@getGlobalNavigation');
    Route::get('site/recent-images', 'SiteController@getRecentImages');
    Route::get('site/recent-posts', 'SiteController@getRecentPosts');
});
