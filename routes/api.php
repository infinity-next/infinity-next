<?php

/**
 * Content API
 */
Route::group(['as' => 'site', 'namespace' => "Content",], function () {
    // boardlist get
    Route::get('board-details.json', 'BoardlistController@getDetails');
    // boardlist search
    Route::post('board-details.json', 'BoardlistController@getDetails');

    // overboard update
    Route::get('overboard.json', 'MultiboardController@getOverboard');
});

/**
 * Multiboard API
 */
 Route::group(['prefix' => '*', 'as' => 'overboard', 'namespace' => "Content",], function () {
     Route::get('{boards}/catalog.json', ['uses' => 'MultiboardController@getOverboardCatalogWithBoards',]);
     Route::get('{worksafe}/catalog.json', ['uses' => 'MultiboardController@getOverboardCatalogWithWorksafe',]);
     Route::get('{worksafe}/{boards}/catalog.json', ['uses' => 'MultiboardController@getOverboardCatalog',]);
     Route::get('catalog.json', ['uses' => 'MultiboardController@getOverboardCatalogAll',]);

     Route::get('{boards}.json', ['uses' => 'MultiboardController@getOverboardWithBoards',]);
     Route::get('{worksafe}.json', ['uses' => 'MultiboardController@getOverboardWithWorksafe',]);
     Route::get('{worksafe}/{boards}.json', ['uses' => 'MultiboardController@getOverboard',]);
     Route::get('.json', ['uses' => 'MultiboardController@getOverboard',]);
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
    Route::put('thread/{post_id}.json', 'BoardController@putReply')
        ->name('thread.reply');

    // Get single thread.
    Route::get('thread/{post_id}.json', 'BoardController@getThread')
        ->name('thread');

    // Get single post.
    Route::get('post/{post_id}.json', ['as' => 'post', 'uses' => 'BoardController@getPost']);
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
        Route::get('{board}/res/{post_id}.json', 'BoardController@getThread');
    });
}
