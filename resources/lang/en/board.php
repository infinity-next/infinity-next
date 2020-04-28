<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Thread, & Post Language File
    |--------------------------------------------------------------------------
    |
    | Any language content regarding the static, final print of threads and
    | posts belong here.
    |
    */

    /**
     * Create a Thread / Post a Reply Form
     */
    // Form Legends
    // These appear above a post form.
    'legend'            => [
        "edit"   => "Edit Post",
        "reply"  => "Reply",
        "thread" => "New Thread",

        'ban' => "Ban details",
        'post_retention' => "Post retention",
        'scope' => "Action scope",

        'report'            => "Report post",
        'report+global'     => "Report post to global staff",

        'verify_pass'       => "Verify Action with Password",
        'verify_mod'        => "Verify Action as Moderator",
    ],

    // Form Fields
    // Specific fields in the form
    'field'               => [
        'subject'         => "Subject",
        'author'          => "Author",
        'email'           => "Email",
        'capcode'         => "No capcode",
        'flag'            => "Flag",
        'password'        => "Password",

        'file-dz'         => "Click or drag files here to upload",
        'spoilers'        => "Spoilers",

        'ip'              => "IP",
        'ip_range'        => "Range",
        'justification'   => "Reason",
        'expires'         => "Ban Expiry",
        'expires-days'    => "Days",
        'expires-hours'   => "Hours",
        'expires-minutes' => "Minutes",

        'expand'          => "Expand",
        'expand-all'      => "Expand All",
        'collapse'        => 'collapse',
        'collapse-all'    => "Collapse All",
        'download'        => "Download",
        'download-all'    => "Download All",
        'unspoiler'       => "Unspoiler",
        'unspoiler-all'   => "Unspoiler All",
        'spoiler'         => "Spoiler",
        'spoiler-all'     => "Spoiler All",
        'remove'          => "Remove",
        'remove-all'      => "Remove All",
    ],

    // Form Submit Buttons
    'submit'            => [
        'edit'              => "Submit Modification",
        'reply'             => "Post Reply",
        'thread'            => "Create Thread",
        'confirm'           => "Confirm Action",

        'report'            => "Report post",
        'report+global'     => "Report post to global staff",
        'verify_mod'        => "Confirm Moderator Action",
        'verify_password'   => "Submit Password",
    ],

    /**
     * Mod Tools
     */
    'report'            => [
        'success'     => "Report submitted successfully.",

        'desc-local'  => "Board staff guidelines for reports ...",
        'local'       => "You are reporting a post to the local board management. " .
            "This usually means that the post is in violation of board-specific rules, " .
            "disparages the spirit of the board, or disrupts conversation.",


        'desc-global' => "Guidelines for global reports ...",
        'global'      => "You are reporting this post to <strong>global management</strong>. " .
            "If a post is in violation of a rule applied to all boards on a site, this is the appropriate action. " .
            "More frivilous or board-specific rule violations should be handled by board staff.",

        'associate'            => "Associate report with your account",
        'associate-no-acct'    => "Register an account to take credit for your reports",
        'associate-disclaimer' => "<p>Any reports associated with your account will follow you. " .
            "Whether the report results in action will become available information to board owners or administrators if you apply for staff positions. " .
            "This success ratio may increase (or decrease) the likelihood of you being accepted into that role.</p>" .
            "<p>Your reports are not public information. Your identity will not be shown alongside your report. " .
            "Your report history will not become available unless you apply for a staff position and opt in to have it displayed.</p>" .
            "<p>If you do not want to have this report associated with your account, do not check the checkbox.</p>",

        'is_not_associated'    => "Anonymous report",
        'is_associated'        => "User associated report",

        'pending'     => "Your report has been received and is awaiting review.",
        'dismissed'   => "The report has been dismissed without action.",
        'successful'  => "The reported post has been dealt with.",

        'reason'      => "Reason",
    ],

    /**
     * Post View
     */
    // Default Values
    'anonymous'         => "Anonymous",
    'you'               => "You",
    'robot'             => "ROBOT",

    // The direct link to a post, like No. 11111
    'post_number'       => "No.",

    // Details
    'detail'            => [
        'sticky'     => "Stickied",
        'bumplocked' => "Bumplocked",
        'locked'     => "Locked",
        'deleted'    => "Deleted",
        'history'    => "View author history",

        'catalog_stats' => "{0}R: :reply_count / F: :file_count|{1}R: :reply_count / F: :file_count / P: :page",

        // Translator's Note:
        // This is a bit silly. It just means the poster
        // found the site via "Adventure!" mode. This can be
        // translated to anything else.
        'adventurer' => "They came from outer space!",
    ],

    // Post Actions
    'action'            => [
        'view'              => "Open",    // Open a thread in the catalog
        'open'              => "Actions", // List of actions
        'close'             => "Close",

        'global'            => "Site-wide",

        'moderate'          => "Moderate",
        'ban'               => "Ban",
        'ban_global'        => "Ban Site-wide",
        'ban_delete'        => "Ban &amp; Delete",
        'ban_delete_board'  => "Ban &amp; Delete Board-wide",
        'ban_delete_global' => "Ban &amp; Delete Site-wide",

        'fuzzyban'          => "Perceptual Hash Ban",
        'bumplock'          => "Bumplock",
        'unbumplock'        => "Un-Bumplock",
        'keep'              => "Keep",
        'delete'            => "Delete",
        'delete_all'        => "Delete All",
        'feature_global'    => "Feature Site-wide",
        'lock'              => "Lock",
        'unlock'            => "Unlock",
        'edit'              => "Edit",
        'sticky'            => "Sticky",
        'unsticky'          => "Unsticky",
        'feature'           => "Feature",
        'unfeature'         => "Unfeature",
        'report'            => "Report",
        'report_global'     => "Report Globally",
        'history'           => "Post History on /:board_uri/",
        'history_global'    => "Post History Site-Wide"
    ],

    'ban'               => [
        'no_ip'        => "There is no IP associated with this post which you can ban.",

        // The number of IP addresses affected by a range ban.
        'ip_range_32'  => "{0}All IPv4 Addresses|[1,31]/:mask (:ips IPs)|{32}/:mask (:ips IP)",
        'ip_range_128' => "{0}All IPv6 Addresses|[1,127]/:mask (:ips IPs)|{128}/:mask (:ips IP)",
    ],

    'meta' => [
        'banned'      => "User was banned for this post",
        'banned_for'  => "User was banned for this post. Reason: <em>:reason</em>",
        'banned_meme' => "USER WAS BANNED FOR THIS POST",
        'updated_by'  => "This post was last edited by :name at :time.",
        'signed'      => "Message signed and validated by the server, but it can make no assurance who posted it. " .
            "<a href=\":signature\" target=\"_new\">View raw message.</a> " .
            "<a href=\":publickey\" target=\"_new\">View publickey.</a>",
    ],


    /**
     * Thread View
     */
     // These fit together as "Omitted 3 posts" or "Omitted 3 posts with 2 files"
     // with pluralized localizations.
    'omitted'           => [
        'text' => [
            'only' => "Omitted :text_posts",
            'both' => "Omitted :text_posts with :text_files",
        ],

        'count' => [
            'replies' => "{0}unknown posts|{1}:count post|[2,*]:count posts",
            'files'   => "{0}unknown files|{1}:count file|[2,*]:count files",
        ],

        'show' => [
            'reply'  => "Reply",
            'open'   => "Last :count",
            'inline' => "Click to expand",
        ]
    ],

    'preview_see_more'  => "Post was truncated. <a href=\":url\">Click here</a> to view the full text",

    /**
     * Page / Action Buttons
     */
    'button'   => [
        'index'   => "Index",
        'catalog' => "Catalog",
        'logs'    => "Logs",
        'gotop'   => "Go to Top",
        'gobot'   => "Go to Bottom",

        'reply'   => "Post a Reply",
        'thread'  => "Start a New Thread",
    ],

    /**
     * Pagination
     */
    // These are the titles that appear when hovering over items.
    'first'    => 'First',
    'previous' => 'Previous',
    'next'     => 'Next',
    'last'     => 'Last',


    /**
     * SFW
     */
    'sfw'      => "Safe for work only",
    'nsfw'     => "Not safe for work allowed",


    /**
     * Logs
     */
    'logs' => [
        'title' => "/:board_uri/ Staff Logs",

        'table' => [
            'time'   => "Time",
            'user'   => "Moderator",
            'action' => "Action",
        ],
    ],

    /**
     * Post Histories
     */
    'history' => [
        'title' => "Post History for :ip",

        // Translator's Note:
        // This is a special quote from UNIX-based operating systems.
        // When accessing another user's account, you receive this message the
        // first time you do so. This mesage has a sort of reverence.
        // Please preverse whitespace (spaces) and my \n. Do not use <br />.
        'lecture' => "We trust you have received the usual lecture from the local System" .
            "\nAdministrator. It usually boils down to these three things:" .
            "\n" .
            "\n    #1) Respect the privacy of others." .
            "\n    #2) Think before you type." .
            "\n    #3) With great power comes great responsibility.",
    ],

    /**
     * Public Config
     */
    'config'     => [
        'title' => "/:board_uri/ Configuration",

        'table' => [
            'option' => "Option",
            'value'  => "Value",
        ],
    ],

    /**
     * Verification
     */
    'verify'     => [
        'title' => "Verify Action",
        'mod'   => "Moderator actions are recorded in the logs.",
    ],

    /**
     * Other
     */
    'overboard'  => "Overboard",
    'multiboard' => "Multiboard",

    'landing'    => [
        'thread_submitted' => "Thread submitted! Redirecting ...",
        'reply_submitted'  => "Reply submitted! Redirecting ...",
    ],
];
