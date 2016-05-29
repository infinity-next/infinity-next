<?php

/*
| Board API Routes (JSON)
*/
Route::group([
    'namespace' => "API\Board",
], function () {
    // Gets the first page of a board.
    Route::any('index.json', 'BoardController@getIndex');

    // Gets index pages for the board.
    Route::get('page/{id}.json', 'BoardController@getIndex');

    // Gets all visible OPs on a board.
    Route::any('catalog.json', 'BoardController@getCatalog');

    // Gets all visible OPs on a board.
    Route::any('config.json', 'BoardController@getConfig');

    // Put new thread
    Route::put('thread.json', 'BoardController@putThread');

    // Put reply to thread.
    Route::put('thread/{post}.json', 'BoardController@putThread');

    // Get single thread.
    Route::get('thread/{post}.json', 'BoardController@getThread');

    // Get single post.
    Route::get('post/{post}.json', 'BoardController@getPost');
});

/*
| Legacy API Routes (JSON)
*/
if (env('LEGACY_ROUTES', false)) {
    Route::group([
        'namespace' => "API\Legacy",
    ], function () {
        // Gets the first page of a board.
        Route::any('index.json', 'BoardController@getIndex');

        // Gets index pages for the board.
        Route::get('{id}.json', 'BoardController@getIndex');

        // Gets all visible OPs on a board.
        Route::any('threads.json', 'BoardController@getThreads');

        // Get single thread.
        Route::get('res/{post}.json', 'BoardController@getThread');
    });
}
