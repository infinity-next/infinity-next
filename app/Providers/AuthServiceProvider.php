<?php

namespace App\Providers;

use App\Board;
use App\Post;
use App\Report;
use App\Contracts\Auth\Permittable as User;
use App\Auth\IneloquentUserProvider;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Auth;
use Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Board::class  => \App\Policies\BoardPolicy::class,
        Post::class   => \App\Policies\PostPolicy::class,
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

        Auth::provider('ineloquent', function ($app, array $config) {
            return new IneloquentUserProvider($this->app['hash'], $config['model']);
        });

        Gate::define('capcode', function (?User $user, Board $board, ?Post $post = null) {
            if ($post instanceof Post) {
                return Response::deny('auth.post.cannot_edit_capcode');
            }

            if ($user->getCapcodes($board)->count() > 0) {
                return Response::allow();
            }

            return Response::deny();
        });

        Gate::define('be-accountable', function (?User $user) {
            return $user->isAccountable();
        });

        Gate::define('bypass-captcha', function (?User $user) {
            // Check if site requires captchas.
            if (!site_setting('captchaEnabled')) {
                return Response::allow();
            }

            // All Tor users must pass a captcha if possible.
            if (!$user->can('be-accountable')) {
                return Response::deny('auth.captcha.unaccountable');
            }

            // Check if this user belongs to a group which can bypass the captcha.
            if ($user->permission('sys.nocaptcha')) {
                return Response::allow();
            }

            // Begin to check captchas for last answers.
            $ip = new IP;
            $session_id = Session::getId();

            $lastCaptcha = Captcha::select('created_at', 'cracked_at')
                ->where(function ($query) use ($ip, $session_id) {
                    // Find captchas answered by this user.
                    $query->where('client_session_id', hex2bin($session_id));

                    // Pull the lifespan of a captcha.
                    // This is the number of minutes between successful entries.
                    $captchaLifespan = (int) site_setting('captchaLifespanTime', 0);

                    if ($captchaLifespan > 0) {
                        $query->whereNotNull('cracked_at');
                        $query->where('cracked_at', '>=', now()->subMinutes($captchaLifespan));
                    }
                })
                ->orderBy('cracked_at', 'desc')
                ->first();

            $requireCaptcha = !($lastCaptcha instanceof Captcha);

            if (!$requireCaptcha) {
                $captchaLifespan = (int) site_setting('captchaLifespanPosts');

                if ($captchaLifespan > 0) {
                    $postsWithCaptcha = Post::select('created_at')
                        ->where('author_ip', $ip)
                        ->where('created_at', '>=', $lastCaptcha->created_at)
                        ->count();

                    if ($postsWithCaptcha >= $captchaLifespan) {
                        return Response::deny(trans_choice('auth.captcha.lifespan', $captchaLifespan));
                    }
                }
                else {
                    return Response::deny(trans_choice('auth.captcha.lifespan', $captchaLifespan));
                }
            }

            return Response::allow();
        });

        Gate::define('create-attachment', function(?User $user)
        {
            return $user->permission('site.attachment.create')
                ? Response::allow()
                : Response::deny('auth.board.cannot_ban');
        });

        Gate::define('global-ban', function(?User $user)
        {
            return ($user->permission('board.user.ban.free') || $user->permission('board.user.ban.reason'))
                ? Response::allow()
                : Response::deny('auth.board.cannot_ban');
        });

        Gate::define('global-delete', function(?User $user)
        {
            return $user->permission('board.post.delete.other', null)
                ? Response::allow()
                : Response::deny('auth.board.cannot_delete');
        });

        Gate::define('global-history', function(?User $user)
        {
            return $user->permission('board.history', null)
                ? Response::allow()
                : Response::deny('auth.board.cannot_view_history');
        });

        Gate::define('ip-address', function(?User $user)
        {
            return $user->permission('site.user.raw_ip')
                ? Response::allow()
                : Response::deny('auth.board.cannot_view_ip_address');
        });

        Gate::define('admin-config', function(?User $user)
        {
            return $user->permission('sys.config')
             ? Response::allow()
             : Response::deny('auth.site.cannot_admin_config');
        });

        Gate::define('admin-permissions', function(?User $user)
        {
            return $user->permission('sys.permissions')
             ? Response::allow()
             : Response::deny('auth.site.cannot_admin_permissions');
        });

        Gate::define('admin-tools', function(?User $user)
        {
            return $user->permission('sys.tools')
             ? Response::allow()
             : Response::deny('auth.site.cannot_admin_tools');
        });

        Gate::define('admin-users', function(?User $user)
        {
            return $user->permission('sys.users')
             ? Response::allow()
             : Response::deny('auth.site.cannot_admin_users');
        });
    }
}
