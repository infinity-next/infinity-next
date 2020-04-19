<?php

/**
 * Control panel sections that do not require authentication..
 *
 * @category   Routes
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2020 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */

/**
 * Bans and Appeals
 */
Route::group(['prefix' => 'bans',], function () {
    Route::get('banned', ['as' => 'banned', 'uses' => 'BansController@getIndexForSelf']);

    Route::get('board/{board}/{ban}', ['as' => 'board.ban', 'uses' => 'BansController@getBan']);
    Route::put('board/{board}/{ban}', ['as' => 'board.ban.appeal', 'uses' => 'BansController@putAppeal']);
    Route::get('board/{board}', ['as' => 'board.bans', 'uses' => 'BansController@getBoardIndex']);

    Route::get('global/{ban}', ['as' => 'site.ban', 'uses' => 'BansController@getBan']);
    Route::put('global/{ban}', ['as' => 'site.ban.appeal', 'uses' => 'BansController@putAppeal']);
    Route::get('/', ['as' => 'site.bans', 'uses' => 'BansController@getIndex']);
});
