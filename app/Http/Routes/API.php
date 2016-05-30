<?php

/**
 * Content API
 */
Route::group([], function () {
    // boardlist get
    Route::get('board-details.json', 'BoardlistController@getDetails');
    // boardlist search
    Route::post('board-details.json', 'BoardlistController@getDetails');

    // overboard update
    Route::get('overboard.json', 'MultiboardController@getOverboard');
});

/**
 * Board API
 */
Route::group(['as' => 'board.', 'prefix' => '{board}', 'namespace' => "Board",], function () {
    // Gets the first page of a board.
    Route::any('index.json', ['as' => 'index', 'uses' => 'BoardController@getIndex']);

    // Gets index pages for the board.
    Route::get('page/{id}.json', ['as' => 'page', 'uses' => 'BoardController@getIndex']);

    // Gets all visible OPs on a board.
    Route::any('catalog.json', ['as' => 'catalog', 'uses' => 'BoardController@getCatalog']);

    // Gets all visible OPs on a board.
    Route::any('config.json', ['as' => 'config', 'uses' => 'BoardController@getConfig']);

    // Put new thread
    Route::put('thread.json', ['as' => 'thread.put', 'uses' => 'BoardController@putThread']);

    // Put reply to thread.
    Route::put('thread/{post}.json', ['as' => 'thread.reply', 'uses' => 'BoardController@putThread']);

    // Get single thread.
    Route::get('thread/{post}.json', ['as' => 'thread', 'uses' => 'BoardController@getThread']);

    // Get single post.
    Route::get('post/{post}.json', ['as' => 'post', 'uses' => 'BoardController@getPost']);
});

/*
| Legacy API Routes (JSON)
*/
if (env('LEGACY_ROUTES', false)) {
    Route::group(['namespace' => "Legacy",], function () {
        // Gets the first page of a board.
        Route::any('{board}/index.json', 'BoardController@getIndex');

        // Gets index pages for the board.
        Route::get('{board}/{id}.json', 'BoardController@getIndex');

        // Gets all visible OPs on a board.
        Route::any('{board}/threads.json', 'BoardController@getThreads');

        // Get single thread.
        Route::get('{board}/res/{post}.json', 'BoardController@getThread');
    });
}
