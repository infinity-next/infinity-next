<?php

namespace App\Providers;

use App\Ban;
use App\BanAppeal;
use App\Board;
use App\Option;
use App\Post;
use App\Report;
use App\Auth\IneloquentUserProvider;
use App\Contracts\Auth\Permittable as User;
use App\Support\IP;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Auth;
use Cache;
use Gate;
use Session;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Ban::class  => \App\Policies\BanPolicy::class,
        BanAppeal::class  => \App\Policies\BanAppealPolicy::class,
        Board::class => \App\Policies\BoardPolicy::class,
        Post::class => \App\Policies\PostPolicy::class,
        Report::class => \App\Policies\ReportPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        $this->registerProviders();
        $this->registerGates();
    }

    public function registerProviders()
    {
        Auth::provider('ineloquent', function ($app, array $config) {
            return new IneloquentUserProvider($this->app['hash'], $config['model']);
        });
    }

    public function registerGates()
    {
        Gate::define('admin-config', function(User $user)
        {
            return $user->permission('sys.config')
             ? Response::allow()
             : Response::deny('auth.site.cannot_admin_config');
        });

        Gate::define('admin-permissions', function(User $user)
        {
            return $user->permission('sys.permissions')
             ? Response::allow()
             : Response::deny('auth.site.cannot_admin_permissions');
        });

        Gate::define('admin-tools', function(User $user)
        {
            return $user->permission('sys.tools')
             ? Response::allow()
             : Response::deny('auth.site.cannot_admin_tools');
        });

        Gate::define('admin-users', function(User $user)
        {
            return $user->permission('sys.users')
             ? Response::allow()
             : Response::deny('auth.site.cannot_admin_users');
        });

        Gate::define('capcode', function (User $user, Board $board, ?Post $post = null) {
            if ($post instanceof Post) {
                return Response::deny('auth.post.cannot_edit_capcode');
            }

            if ($user->getCapcodes($board)->count() > 0) {
                return Response::allow();
            }

            return Response::deny();
        });

        Gate::define('be-accountable', function (User $user) {
            return $user->isAccountable();
        });

        Gate::define('bypass-captcha', function (User $user) {
            // All Tor users must pass a captcha if possible.
            if (!$user->can('be-accountable')) {
                return Response::deny('auth.captcha.unaccountable');
            }

            // Check if site requires captchas.
            if (!site_setting('captchaEnabled')) {
                return Response::allow();
            }

            // Check if this user belongs to a group which can bypass the captcha.
            if ($user->permission('sys.nocaptcha')) {
                return Response::allow();
            }

            // Check to see if we have any grace left for our session
            if (!Cache::has("captcha.grace." . Session::getId())) {
                return Response::deny(trans('auth.captcha.lifespan'));
            }

            return Response::allow();
        });

        Gate::define('create-attachment', function(User $user)
        {
            return $user->permission('site.attachment.create')
                ? Response::allow()
                : Response::deny('auth.site.cannot_upload_files');
        });

        Gate::define('ban-file', function(User $user)
        {
            return $user->permission('site.attachment.ban')
                ? Response::allow()
                : Response::deny('auth.site.cannot_upload_files');
        });

        Gate::define('global-ban', function(User $user)
        {
            return ($user->permission('board.user.ban.free') || $user->permission('board.user.ban.reason'))
                ? Response::allow()
                : Response::deny('auth.board.cannot_ban');
        });

        Gate::define('global-bumplock', function(User $user)
        {
            return $user->permission('board.post.suppress')
                ? Response::allow()
                : Response::deny('auth.board.cannot_ban');
        });

        Gate::define('global-delete', function(User $user)
        {
            return $user->permission('board.post.delete.other')
                ? Response::allow()
                : Response::deny('auth.board.cannot_delete');
        });

        Gate::define('global-history', function(User $user)
        {
            return $user->permission('board.history')
                ? Response::allow()
                : Response::deny('auth.board.cannot_view_history');
        });

        Gate::define('global-report', function(User $user)
        {
            return $user->permission('site.post.report')
                ? Response::allow()
                : Response::deny('auth.board.cannot_view_history');
        });

        Gate::define('ip-address', function(User $user)
        {
            return $user->permission('site.user.raw_ip')
                ? Response::allow()
                : Response::deny('auth.board.cannot_view_ip_address');
        });

        Gate::define('register', function(User $user)
        {
            return $user->permission('site.user.create');
        });

        Gate::define('setting-lock', function(User $user, Option $option)
        {
            return $user->permission('site.board.setting_lock')
                ? Response::allow()
                : Response::deny();
        });

        Gate::define('viewTelescope', function ($user) {
            return $user->permission('sys.config');
        });
    }
}
