<?php

/**
 * Handles routing for frontend services and content.
 *
 * @category   Routes
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */

/*
| API
*/
Route::group([
    'namespace' => 'API',
], function () {
    Route::get('board-details.json', 'BoardlistController@getDetails');
    Route::post('board-details.json', 'BoardlistController@getDetails');

    Route::get('overboard.json', 'MultiboardController@getOverboard');
});
