<?php

return [

        //'arabic' => (n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 ? 4 : 5);

        //nplurals=3; plural=(n==1 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);
        //jabłko 1 jabłko, 2 jabłka, 3 jabłka, 4 jabłka, 5 jabłek, 6 jabłek, 7 jabłek
        'polish' => ":n jabłko|:n jabłka|:n jabłek",
    /*
    |--------------------------------------------------------------------------
    | Widgets
    |--------------------------------------------------------------------------
    |
    | Any language content regarding JavaScript enabled features
    |
    */

    /**
     * Main JavaScript Widget Master
     */
    'main' => [
        'title' => "Main Configuration",

        'desc' => "These options are for core components of the website.",

        'option' => [
            'widget' => "Enable widgets",
        ],
    ],

    /**
     * Auto-updater
     */
    'autoupdater' => [
        'title' => "Thread Autoupdater",

        'enable' => "Stream new replies",
        'update' => "Update",
        'updating' => "Updating ...",

        'websocket' => "You will receive new posts automatically.",
        'websocket_dc' => "You are disconnected from the websocket.",
    ],

    /**
     * Content Preferences
     */
    'content' => [
        'title' => "Content",

        'option' => [
            'sfw' => "Safe-for-Work Mode",
        ],
    ],

    /**
     * Dice
     */
    'dice' => [
        'rolling' => 'Rolling',
        'flipping' => 'Flipping',

        'main' => "Rolling D dice with N sides.",
        'modifier' => "Adding / subtrating this value from the final total.",
        'minimum' => "Rolls below this value are re-rolled.",
        'maximum' => "Rolls above this value are re-rolled.",
        'greater_than' => "Rolls smaller than this value are not totalled.",
        'less_than' => "Rolls larger than this value are not totalled.",

        'total' => "Effective net value of rolls with rules considered.",
    ],

    /**
     * Lazy Image Loader
     */
    'lazyimg' => [
        'title' => "Lazy Images",

        'desc' => "Thumbnails can be set to not load until you can see them. " .
            "This can improve your overall render times and save bandwidth.",

        'option' => [
            'enable' => "Lazy load thumbnails",
        ],
    ],

    /**
     * InstantClick One-Page Application
     */
    'instantclick' => [
        'title' => "InstantClick",

        'desc' => "InstantClick will turn the site into a one-page " .
            "application, and most subsequent link clicks will cause the page " .
            "to load new contents without refreshing the entire document. " .
            "This can enable pages to load instantly, but on older machines " .
            "it may cause resource issues and edge-case errors.<br />".
            "<strong>Experimental, use at your own risk.</strong>",

        'option' => [
            'enable' => "Enable InstantClick",
        ],
    ],

    /**
     * Posts
     */
    'post' => [
        'title' => "Posts",

        'desc' => "The post widget is very large and incorporates many " .
            "fundamental aspects to how an imageboard looks and feels.",

        'option' => [
            'author_id' => "Display Author IDs when available",
            'attachment_preview' => "Show Image Preview on Hover"
        ],
    ],


    /**
     * Postbox
     */
    'postbox' => [
        'title' => "Post Form",

        'option' => [
            'password'  => "Default post deletion password",
        ],
    ],

    /**
     * Stylist
     */
    'stylist' => [
        'title'  => "Stylist",
        'option' => [
            'board' => "Prioritize Board CSS",
            'theme' => "Theme Base",
            'css'   => "Custom Styling",
        ],
    ],

    /**
     * Timestamps
     */
    'time' => [
        'title' => "Timestamps",

        'option' => [
            'format' => "Timestamp Format",
        ],

        // Default PHP formatting. Different than JS.
        'format' => "%Y-%b-%d %H:%M:%S",

        'calendar' => [
            // Order of these item's children are important.
            // 0 must be January. 11 must be December.
            // 0 must be Sunday. 6 must be Saturday.
            'months' => [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December",
            ],
            'abbrevmonths' => [
                "Jan", "Feb", "Mar", "Apr",
                "May", "Jun", "Jul", "Aug",
                "Sep", "Oct", "Nov", "Dec",
            ],
            'weekdays' => [
                "Sunday",
                "Monday",
                "Tuesday",
                "Wednesday",
                "Thursday",
                "Friday",
                "Saturday",
            ],
            'abbrevdays' => [
                "Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat",
            ],
        ]
    ],
];
