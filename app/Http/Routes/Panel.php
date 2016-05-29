<?php

/**
 * Control panel, authentication, board configuration, site administration.
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
 * Authentication
 */
// login
Route::get('login',    ['as' => 'login', 'uses' => 'AuthController@getLogin',]);
// login attempt
Route::post('login',   ['as' => 'login.attempt', 'uses' => 'AuthController@postLogin',]);
// logout
Route::get('logout',   ['as' => 'logout', 'uses' => 'AuthController@getLogout',]);
// register
Route::get('register', ['as' => 'register', 'uses' => 'AuthController@getRegister',]);
// register create
Route::put('register', ['as' => 'register.create', 'uses' => 'AuthController@putRegister',]);

/**
 * Account Recovery
 */
Route::group(['prefix' => 'password', 'as' => 'password'], function()
{
    // password
    Route::get('/', ['uses' => 'PasswordController@getIndex',]);
    // password update
    Route::post('/',['as' => '.update', 'uses' => 'PasswordController@postIndex',]);
    // reset
    Route::get('reset', ['as' => '.reset', 'uses' => 'PasswordController@getReset',]);
    // reset attempt
    Route::post('reset',['as' => '.reset.attempt', 'uses' => 'PasswordController@postReset',]);
    // email
    Route::get('email', ['as' => '.email', 'uses' => 'PasswordController@getEmail',]);
    // email request
    Route::post('email',['as' => '.email.request', 'uses' => 'PasswordController@postEmail',]);
});

/**
 * Post history (IP)
Route::get('history/{ip}', 'HistoryController@getHistory');

    Route::group([
    'prefix' => 'bans',
], function () {
    Route::get('banned', 'BansController@getIndexForSelf');
    Route::get('board/{board}/{ban}', 'BansController@getBan');
    Route::put('board/{board}/{ban}', 'BansController@putAppeal');
    Route::get('global/{ban}', 'BansController@getBan');
    Route::get('board/{board}', 'BansController@getBoardIndex');
    Route::get('global', 'BansController@getGlobalIndex');
    Route::get('/', 'BansController@getIndex');
});
*/

/*
 *  Page Controllers (Panel / Management)
 */
Route::group([ 'middleware' => [ \App\Http\Middleware\BoardAmbivilance::class, ], ], function () {
    Route::get('site/page/{page}/delete', [
        'as' => 'page.delete',
        'uses' => 'PageController@delete',
    ]);
    Route::get('site/pages', ['as' => 'pages', 'uses' => 'PageController@index']);
    Route::resource('site/page', 'PageController', [
        'names' => [
            'index' => 'page.index',
            'create' => 'page.create',
            'store' => 'page.store',
            'show' => 'page.show',
            'edit' => 'page.edit',
            'update' => 'page.update',
            'destroy' => 'page.destroy',
        ],
    ]);
    Route::get('board/{board}/page/{page}/delete', [
        'as' => 'board.page.delete',
        'uses' => 'PageController@delete',
    ]);
    Route::get('board/{board}/pages', ['as' => 'board.pages', 'uses' => 'PageController@index']);
    Route::resource('board/{board}/page', 'PageController', [
        'names' => [
            'index' => 'board.page.index',
            'create' => 'board.page.create',
            'store' => 'board.page.store',
            'show' => 'board.page.show',
            'edit' => 'board.page.edit',
            'update' => 'board.page.update',
            'destroy' => 'board.page.destroy',
        ],
    ]);
});

/**
 * Board Controls
 */
Route::group(['namespace' => 'Boards',], function () {
    /**
     * Board Lists
     */
    Route::group(['as' => 'boards.', 'prefix' => 'boards',], function () {
        Route::get('create', ['as' => 'create', 'uses' => 'BoardsController@getCreate']);
        Route::put('create', ['as' => 'store', 'uses' => 'BoardsController@putCreate']);

        Route::get('assets', ['as' => 'assets', 'uses' => 'BoardsController@getAssets']);
        Route::get('config', ['as' => 'config', 'uses' => 'BoardsController@getConfig']);
        Route::get('staff',  ['as' => 'staff', 'uses' => 'BoardsController@getStaff']);
        Route::get('tags',   ['as' => 'tags', 'uses' => 'BoardsController@getTags']);

        Route::get('/', ['as' => 'index', 'uses' => 'BoardsController@getIndex']);
    });

    /**
     * Ban Appeals
     */
    Route::get('appeals/{board?}', ['as' => 'appeals.index', 'uses' => 'AppealsController@getIndex']);
    Route::patch('appeals/{board?}', ['as' => 'appeals.patch', 'uses' => 'AppealsController@patchIndex']);

    /**
     * Reports
     */
    Route::group(['as' => 'reports.', 'prefix' => 'reports/{board?}'], function () {
        Route::get('{report}/dismiss', ['as' => 'dismiss', 'uses' => 'ReportsController@getDismiss']);
        Route::get('{report}/dismiss-ip', ['as' => 'dismiss.ip', 'uses' => 'ReportsController@getDismissIp']);
        Route::get('{post}/dismiss-post', ['as' => 'dismiss.post', 'uses' => 'ReportsController@getDismissPost']);
        Route::get('{report}/promote', ['as' => 'promote', 'uses' => 'ReportsController@getPromote']);
        Route::get('{post}/promote-post', ['as' => 'promote.post', 'uses' => 'ReportsController@getPromotePost']);
        Route::get('{report}/demote', ['as' => 'demote', 'uses' => 'ReportsController@getDemote']);
        Route::get('{post}/demote-post', ['as' => 'demote.post', 'uses' => 'ReportsController@getDemotePost']);
        Route::get('', ['as' => 'index', 'uses' => 'ReportsController@getIndex']);
    });

    Route::group(['prefix' => 'board/{board}', 'as' => 'board.',], function () {
        /*
         * Board Featuring
         */
        Route::get('feature',  ['as' => 'feature', 'uses' => 'FeatureController@getIndex',]);
        Route::post('feature', ['as' => 'feature.update', 'uses' => 'FeatureController@postIndex',]);

        /**
         * Staffing
         */
        Route::get('roles/create',  ['as' => 'roles.create', 'uses' => 'RolesController@create',]);
        Route::put('roles/create',  ['as' => 'roles.store', 'uses' => 'RolesController@store',]);
        Route::get('roles',         ['as' => 'roles', 'uses' => 'RolesController@get',]);

        /**
         * Roles
         */
        Route::get('role/{role}/delete',        ['as' => 'role.delete', 'uses' => 'RoleController@getDelete',]);
        Route::delete('role/{role}/delete',     ['as' => 'role.destroy', 'uses' => 'RoleController@destroyDelete',]);
        Route::patch('role/{role}',             ['as' => 'role.store', 'uses' => 'RoleController@patchIndex',]);
        Route::get('role/{role}',               ['as' => 'role', 'uses' => 'RoleController@getIndex',]);

        /**
         * Permissions
         */
        Route::get('role/{role}/permissions',   ['as' => 'role.permissions', 'uses' => 'PermissionController@getPermissions',]);
        Route::patch('role/{role}/permissions', ['as' => 'role.permissions.patch', 'uses' => 'PermissionController@patchPermissions',]);

        /**
         * Staffing
         */
        Route::get('staff',         ['as' => 'staff', 'uses' => 'StaffController@getIndex',]);
        Route::get('staff/create',  ['as' => 'staff.create', 'uses' => 'StaffController@getAdd',]);
        Route::post('staff/create', ['as' => 'staff.store', 'uses' => 'StaffController@storeAdd',]);

        /**
         * Tags
         */
         Route::get('tags',      ['as' => 'tags', 'uses' => 'ConfigController@getTags',]);
         Route::put('tags',      ['as' => 'tags.put', 'uses' => 'ConfigController@putTags',]);

         /**
          * Assets
          */
         Route::get('assets',     ['as' => 'assets', 'uses' => 'ConfigController@getAssets',]);
         Route::patch('assets',   ['as' => 'assets.patch', 'uses' => 'ConfigController@patchConfig',]);
         Route::put('assets',     ['as' => 'assets.put', 'uses' => 'ConfigController@patchConfig',]);
         Route::post('assets',    ['as' => 'assets.destroy', 'uses' => 'ConfigController@destroyAssets',]);

        /**
         * Config
         */
         Route::get('config',    ['as' => 'config', 'uses' => 'ConfigController@getConfig',]);
         Route::patch('config',  ['as' => 'config.patch', 'uses' => 'ConfigController@patchConfig',]);
         Route::get('/',         ['as' => 'basic', 'uses' => 'ConfigController@getConfig',]);
         Route::patch('/',       ['as' => 'basic.patch', 'uses' => 'ConfigController@patchConfig',]);
    });
});

Route::group(['namespace' => 'Site', 'as' => 'site.', 'prefix' => 'site',], function () {
    Route::get('/', ['as' => 'index', 'uses' => 'SiteController@getIndex',]);

    Route::get('phpinfo', ['as' => 'phpinfo', 'uses' => 'SiteController@getPhpinfo']);

    Route::controllers([
        'config' => 'ConfigController',
    ]);
});

/*
    Route::group([
    'namespace' => 'Users',
    'prefix' => 'users',
], function () {
    Route::get('/', 'UsersController@getIndex');
});

    Route::group([
    'namespace' => 'Roles',
    'prefix' => 'roles',
], function () {
    Route::controller('permissions/{role}', 'PermissionsController');
    Route::get('permissions', 'RolesController@getPermissions');
});

// /cp/adventure forwards you to a random board.
Route::controller('adventure', 'AdventureController');
*/

/**
 * Landing page
 */
// home
Route::get('/', ['as' => 'home', 'uses' => 'HomeController@getIndex',]);
