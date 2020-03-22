<?php

/**
 * Board, threads, and accessory pages.
 *
 * @category   Routes
 *
 * @author     Joshua Moon <josh@jaw.sh>
 * @copyright  2016 Infinity Next Development Group
 * @license    http://www.gnu.org/licenses/agpl-3.0.en.html AGPL3
 *
 * @since      0.6.0
 */

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
Route::group(['as' => 'post.', 'prefix' => 'post/{post_id}',], function () {
    // history
    Route::get('history', ['as' => 'history', 'uses' => 'HistoryController@list',]);

    // mod (delete, ban)
    Route::get('moderate', ['as' => 'mod', 'uses' => 'PostController@moderate',]);
    Route::post('moderate', ['as' => 'mod.issue', 'uses' => 'PostController@issue',]);

    // report
    Route::get('report', ['as' => 'report', 'uses' => 'PostController@report']);
    Route::post('report', ['as' => 'put.report', 'uses' => 'PostController@flag']);
    Route::get('report/global', ['as' => 'report.global', 'uses' => 'PostController@report']);
    Route::post('report/global', ['as' => 'put.report.global', 'uses' => 'PostController@flag']);

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
    Route::put('thread/{post_id}', ['as' => '.reply', 'uses' => 'BoardController@putThread',]);

    // submission success (redirect to created post)
    Route::get('redirect/{post_id}', ['as' => '.redirect', 'uses' => 'BoardController@getThreadRedirect', ]);

    // post shortcut
    Route::get('post/{post_id}', ['as' => '.goto', 'uses' => 'BoardController@getThread',]);

    // thread get
    Route::get('thread/{post_id}', ['uses' => 'BoardController@getThread',]);
    Route::get('thread/{post_id}/{splice}', ['uses' => 'BoardController@getThread',]);
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
