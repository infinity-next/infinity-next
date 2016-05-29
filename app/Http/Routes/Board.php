<?php

/**
 * Post history
 */
Route::get('history/{post}', 'Panel\HistoryController@getBoardHistory');

/*
| Legacy Redirects
*/
if (env('LEGACY_ROUTES', false)) {
    Route::any('index.html', function (App\Board $board) {
        return redirect("{$board->board_uri}");
    });
    Route::any('catalog.html', function (App\Board $board) {
        return redirect("{$board->board_uri}/catalog");
    });
    Route::any('{id}.html', function (App\Board $board, $id) {
        return redirect("{$board->board_uri}/{$id}");
    });
    Route::any('res/{id}.html', function (App\Board $board, $id) {
        return redirect("{$board->board_uri}/thread/{$id}");
    });
    Route::any('res/{id}+{last}.html', function (App\Board $board, $id, $last) {
        return redirect("{$board->board_uri}/thread/{$id}/{$last}");
    })->where(['last' => '[0-9]+']);
}


/*
| Board Post Routes (Modding)
*/
Route::group([
    'prefix' => 'post/{post}',
], function () {
    Route::controller('', 'PostController');
});


/**
 * Threads & Replies
 */
Route::group(['as' => 'thread',], function () {
    // thread put
    Route::put('thread', ['as' => '.put', 'uses' => 'BoardController@putThread',]);

    // reply put
    Route::put('thread/{post}', ['as' => '.reply', 'uses' => 'BoardController@putThread',]);

    // submission success (redirect to created post)
    Route::get('redirect/{post}', ['as' => '.redirect', 'uses' => 'BoardController@getThreadRedirect', ]);

    // post shortcut
    Route::get('post/{post}', ['as' => '.goto', 'uses' => 'BoardController@getThread',]);

    // thread get
    Route::get('thread/{post}/{splice?}', ['uses' => 'BoardController@getThread',]);
});

// stylesheet
Route::get('{style}.css', [ 'as' => 'style', 'uses' => 'BoardController@getStylesheet']);
Route::get('{style}.txt', [ 'as' => 'style.raw', 'uses' => 'BoardController@getStylesheetAsText']);

// public config
Route::get('config', ['as' => 'config', 'uses' => 'BoardController@getConfig']);
// public logs
Route::get('logs', ['as' => 'logs', 'uses' => 'BoardController@getLogs']);

// post preview
Route::any('post/preview', ['as' => 'post.preview', 'uses' => 'PostController@anyPreview']);

// file hash check
Route::get('check-file', ['as' => 'file.check', 'uses' => 'BoardController@getFile']);
// file upload
Route::post('upload-file', ['as' => 'file.put', 'uses' => 'BoardController@putFile']);

// catalog
Route::get('catalog', ['as' => 'catalog', 'uses' => 'BoardController@getCatalog']);

// board index
Route::any('/{id?}', ['as' => 'index', 'uses' => 'BoardController@getIndex']);
