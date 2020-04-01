<?php

/**
 * Handles routing for media queries.
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
 * Media queries for actual content.
 * Can be routed through a CDN (sub)domain.
 */
if (false && !is_hidden_service() && config('app.url_media', false)) {
    Route::group(['domain' => config('app.url_media')], function() {
        Route::get('{attachment}/{filename}', [
            'as'   => 'file.attachment',
            'uses' => 'FileController@getImageFromAttachment',
        ]);

        Route::get('{hash}/{filename}', [
            'as'   => 'file.hash',
            'uses' => 'FileController@getImageFromHash',
            ])->where(['hash' => "[a-f0-9]{32}",]);

        Route::get('thumb/{attachment}/{filename}', [
            'as'   => 'thumb.attachment',
            'uses' => 'FileController@getThumbnailFromAttachment',
        ]);

        Route::get('thumb/{hash}/{filename}', [
            'as'   => 'thumb.hash',
            'uses' => 'FileController@getThumbnailFromHash',
        ])->where(['hash' => "[a-f0-9]{32}",]);
    });
}
else {
    Route::group(['prefix' => '{board}/file',], function() {
        Route::get('{hash}/{filename}', 'FileController@getImageFromHash')
            ->name('file.hash');

        Route::get('{attachment}/{filename}', 'FileController@getImageFromAttachment')
            ->name('file.attachment');

        Route::get('thumb/{hash}/{filename}', [
            'as'   => 'thumb.hash',
            'uses' => 'FileController@getThumbnailFromHash',
        ]);

        Route::get('thumb/{attachment}/{filename}', [
            'as'   => 'thumb.attachment',
            'uses' => 'FileController@getThumbnailFromAttachment',
        ]);
    });
}
