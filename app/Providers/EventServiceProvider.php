<?php

namespace App\Providers;

use App\Page;
use App\Post;
use App\Observers\PageObserver;
use App\Observers\PostObserver;
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
        // Post specific events
        \App\Events\AttachmentWasModified::class => [
            \App\Listeners\BoardRecachePages::class,
            \App\Listeners\OverboardRecache::class,
            \App\Listeners\ThreadRecache::class,
        ],
        \App\Events\PostWasAdded::class => [
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

        // Board specific events
        \App\Events\BoardWasCreated::class => [
            \App\Listeners\UserRecachePermissions::class,
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
        ],

        // Page specific events
        \Appp\Events\PageWasCreated::class => [],
        \Appp\Events\PageWasModified::class => [],
        \Appp\Events\PageWasDeleted::class => [],

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
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     */
    public function boot(DispatcherContract $events)
    {
        Page::observe(new PageObserver());
        Post::observe(new PostObserver());
        parent::boot($events);
    }
}
