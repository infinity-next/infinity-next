<?php

/**
 * Legacy
 */
if (env('LEGACY_ROUTES', false)) {
    Route::any('index.html', function (App\Board $board) {
        return redirect("{$board->board_uri}", 301);
    });
    Route::any('catalog.html', function (App\Board $board) {
        return redirect("{$board->board_uri}/catalog", 301);
    });
    Route::any('{id}.html', function (App\Board $board, $id) {
        return redirect("{$board->board_uri}/{$id}", 301);
    });
    Route::any('res/{id}.html', function (App\Board $board, $id) {
        return redirect("{$board->board_uri}/thread/{$id}", 301);
    });
    Route::any('res/{id}+{last}.html', function (App\Board $board, $id, $last) {
        return redirect("{$board->board_uri}/thread/{$id}/{$last}", 301);
    })->where(['last' => '[0-9]+']);
}


/**
 * Post Management
 */
Route::group(['as' => 'post.', 'prefix' => 'post/{post}',], function () {
    // history
    Route::get('history', ['as' => 'history', 'uses' => 'HistoryController@list',]);

    // mod (delete, ban)
    Route::get('ban',               ['as' => 'ban', 'uses' => 'PostController@moderate',]);
    Route::put('ban',               ['as' => 'ban.put', 'uses' => 'PostController@issue',]);
    Route::get('delete',            ['as' => 'delete', 'uses' => 'PostController@moderate',]);
    Route::put('delete',            ['as' => 'delete.put', 'uses' => 'PostController@issue',]);
    Route::get('delete/all',        ['as' => 'delete.all', 'uses' => 'PostController@moderate',]);
    Route::put('delete/all',        ['as' => 'delete.all.put', 'uses' => 'PostController@issue',]);
    Route::get('delete/global',     ['as' => 'delete.global', 'uses' => 'PostController@moderate',]);
    Route::put('delete/global',     ['as' => 'delete.global.put', 'uses' => 'PostController@issue',]);
    Route::get('ban/delete',        ['as' => 'ban.delete', 'uses' => 'PostController@moderate',]);
    Route::put('ban/delete',        ['as' => 'ban.delete.put', 'uses' => 'PostController@issue',]);
    Route::get('ban/delete/all',    ['as' => 'ban.delete.all', 'uses' => 'PostController@moderate',]);
    Route::put('ban/delete/all',    ['as' => 'ban.delete.all.put', 'uses' => 'PostController@issue',]);
    Route::get('ban/delete/global', ['as' => 'ban.delete.global', 'uses' => 'PostController@moderate',]);
    Route::put('ban/delete/global', ['as' => 'ban.delete.global.put', 'uses' => 'PostController@issue',]);

    // report
    Route::get('report', ['as' => 'report', 'uses' => 'PostController@report']);
    Route::put('report', ['as' => 'put.report', 'uses' => 'PostController@flag']);
    Route::get('report/global', ['as' => 'report.global', 'uses' => 'PostController@report']);
    Route::put('report/global', ['as' => 'put.report.global', 'uses' => 'PostController@flag']);

    // edit
    Route::get('edit', ['as' => 'edit', 'uses' => 'PostController@edit']);
    Route::patch('edit', ['as' => 'edit.update', 'uses' => 'PostController@update']);

    // stickying
    Route::get('sticky', ['as' => 'sticky', 'uses' => 'PostController@sticky']);
    Route::get('unsticky', ['as' => 'unsticky', 'uses' => 'PostController@unsticky']);

    // lock
    Route::get('lock', ['as' => 'lock', 'uses' => 'PostController@lock']);
    Route::get('unlock', ['as' => 'unlock', 'uses' => 'PostController@unlock']);
    Route::get('bumplock', ['as' => 'bumplock', 'uses' => 'PostController@bumplock']);
    Route::get('unbumplock', ['as' => 'unbumplock', 'uses' => 'PostController@unbumplock']);

    // feature
    Route::get('feature', ['as' => 'feature', 'uses' => 'PostController@feature']);
    Route::get('unfeature', ['as' => 'unfeature', 'uses' => 'PostController@unfeature']);
});

/**
 * Attachment Moderation
 */
Route::group(['as' => 'file.', 'prefix' => 'file/{attachment}',], function () {
     Route::get('remove', ['as' => 'delete', 'uses' => 'AttachmentController@getDeleteAttachment']);
     Route::post('remove', ['as' => 'destroy', 'uses' => 'AttachmentController@postDeleteAttachment']);
     Route::get('spoiler', ['as' => 'spoiler', 'uses' => 'AttachmentController@getSpoilerAttachment']);
     Route::post('spoiler', ['as' => 'spoiler.patch', 'uses' => 'AttachmentController@postSpoilerAttachment']);
     Route::get('unspoiler', ['as' => 'unspoiler', 'uses' => 'AttachmentController@getSpoilerAttachment']);
     Route::post('unspoiler', ['as' => 'unspoiler.patch', 'uses' => 'AttachmentController@postSpoilerAttachment']);
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
