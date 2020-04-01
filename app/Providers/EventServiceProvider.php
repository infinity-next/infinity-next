<?php

namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        /**
         * Board events
         */
        \App\Events\BoardWasCreated::class => [
            \App\Listeners\BoardSetOwner::class,
            \App\Listeners\UserRecachePermissions::class,
            \App\Listeners\LogEvent::class,
        ],
        \App\Events\BoardWasModified::class => [
            \App\Listeners\BoardModelRecache::class,
            \App\Listeners\BoardListRecache::class,
            \App\Listeners\BoardStyleRecache::class,
            \App\Listeners\OverboardRecache::class,
            \App\Listeners\BoardRecachePages::class,
        ],
        \App\Events\BoardWasReassigned::class => [
            \App\Listeners\UserRecachePermissions::class,
            \App\Listeners\LogEvent::class,
        ],

        /**
         * Posts
         */
        \App\Events\AttachmentWasModified::class => [
            \App\Listeners\BoardRecachePages::class,
            \App\Listeners\OverboardRecache::class,
            \App\Listeners\ThreadRecache::class,
        ],
        \App\Events\PostWasCapcoded::class => [
            \App\Listeners\LogEvent::class,
        ],
        \App\Events\PostWasCreated::class => [
            \App\Listeners\BoardRecachePages::class,
            \App\Listeners\OverboardRecache::class,
            \App\Listeners\PostHTMLRecache::class,
            \App\Listeners\ThreadRecache::class,
        ],
        \App\Events\PostWasBanned::class => [
            \App\Listeners\BoardRecachePages::class,
            \App\Listeners\OverboardRecache::class,
            \App\Listeners\ThreadRecache::class,
        ],
        \App\Events\PostWasDeleted::class => [
            \App\Listeners\BoardRecachePages::class,
            \App\Listeners\OverboardRecache::class,
            \App\Listeners\ThreadRecache::class,
        ],
        \App\Events\PostWasModified::class => [
            \App\Listeners\BoardRecachePages::class,
            \App\Listeners\OverboardRecache::class,
            \App\Listeners\PostHTMLRecache::class,
            \App\Listeners\ThreadRecache::class,
        ],
        \App\Events\PostWasModerated::class => [
            \App\Listeners\ReportMarkSuccessful::class,
            \App\Listeners\ThreadRecount::class,

            \App\Listeners\BoardRecachePages::class,
            \App\Listeners\OverboardRecache::class,
            \App\Listeners\PostHTMLRecache::class,
            \App\Listeners\ThreadRecache::class,
        ],

        // Thread (OP) specific Events
        \App\Events\ThreadWasStickied::class => [
            \App\Listeners\BoardRecachePages::class,
            \App\Listeners\ThreadRecache::class,
        ],
        \App\Events\ThreadNewReply::class => [
            \App\Listeners\ThreadAutopruneOnReply::class,
        ],

        // Page specific events
        \Appp\Events\PageWasCreated::class => [],
        \Appp\Events\PageWasModified::class => [],
        \Appp\Events\PageWasDeleted::class => [],

        /**
         * Reports
         */
        \App\Events\ReportWasCreated::class => [],
        \App\Events\ReportWasDeleted::class => [],
        \App\Events\ReportWasDemoted::class => [],
        \App\Events\ReportWasDismissed::class => [],
        \App\Events\ReportWasPromoted::class => [],

        // Role events
        \App\Events\RoleWasModified::class => [
            \App\Listeners\UserRecachePermissions::class,
        ],
        \App\Events\RoleWasDeleted::class => [
            \App\Listeners\UserRecachePermissions::class,
        ],

        // User Events
        \App\Events\UserRolesModified::class => [
            \App\Listeners\UserRecachePermissions::class,
        ],

        // Site Events
        \App\Events\SiteSettingsWereModified::class => [
            \App\Listeners\SiteSettingsRecache::class,
        ],

    ];

    /**
     * Register any other events for your application.
     */
    public function boot()
    {
        \App\Board::observe(new \App\Observers\BoardObserver);
        \App\Page::observe(new \App\Observers\PageObserver);
        \App\Post::observe(new \App\Observers\PostObserver);
        \App\Report::observe(new \App\Observers\ReportObserver);

        parent::boot();
    }
}
