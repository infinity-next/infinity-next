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
        "reply"  => "عمل ريبلاي",
        "thread" => "سوي ثريد جديد",

        "ban"               => "Ban user from :board for post",
        "ban+global"        => "Ban user from entire site for post",
        "ban+delete"        => "Ban user and delete post",
        "all+ban+delete"    => "Ban user and delete all their posts on :board",
        "ban+delete+global" => "Ban user and wipe all their posts from entire site",

        'report'            => "Report post",
        'report+global'     => "Report post to global staff",

        'verify_pass'       => "Verify Action with Password",
        'verify_mod'        => "Verify Action as Moderator",
    ],

    // Form Fields
    // Specific fields in the form
    'field'               => [
        'subject'         => "موضوع",
        'author'          => "الكاتب",
        'email'           => "ايميل",
        'capcode'         => "لات حت شي كبكود",
        'flag'            => "العلم ده",
        'password'        => "البسورد ده",

        'file-dz'         => "فص أو حت الفيلات على البكس ده",
        'spoilers'        => "مخربشه",

        'ip'              => "IP",
        'ip_range'        => "Range",
        'justification'   => "لماذا ما تجدر إتسويه البوستات؟",
        'expires'         => "ينتهي بعد",
        'expires-days'    => "أيام",
        'expires-hours'   => "ساعات",
        'expires-minutes' => "دقايق",

        'expand'          => "كبرها",
        'expand-all'      => "كبرها كوله يا باشا",
        'collapse'        => 'صغره',
        'collapse-all'    => "صغره كوله يا باشا",
        'download'        => "حمل هذل فايل",
        'download-all'    => "حمل كول الفيلات",
        'unspoiler'       => "طلع المخربش",
        'unspoiler-all'   => "طلع كول المخربشات",
        'spoiler'         => "خربشه",
        'spoiler-all'     => "خربش كوله",
        'remove'          => "احذفها",
        'remove-all'      => "إحذف كوله",
    ],

    // Form Submit Buttons
    'submit'            => [
        "edit"   => "Submit Modification",
        "reply"  => "عمل ريبلاي",
        "thread" => "سوي ثريد جديد",

        "ban"               => "Submit :board ban",
        "ban+global"        => "Submit global ban",
        "ban+delete"        => "Submit :board ban and delete post",
        "all+ban+delete"    => "Submit :board ban and delete user's posts",
        "ban+delete+global" => "Submit global ban and wipe user posts",

        'report'            => "عمل بلاغ على هذل ثور",
        'report+global'     => "عمل بلاغ على هذل ثور، و ودي المحكمة",
        'verify_mod'        => "تيكد",
        'verify_password'   => "حت باسورد",
    ],

    /**
     * Mod Tools
     */
    'report'            => [
        'success'     => "ممتاز",

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

        'reason'      => "لماذا يا أخي؟",
    ],

    /**
     * Post View
     */
    // Default Values
    'anonymous'         => "فولان",
    'you'               => "إنت",

    // The direct link to a post, like No. 11111
    'post_number'       => "رقم",

    // Details
    'detail'            => [
        'sticky'     => "معامل جلغ",
        'bumplocked' => "موسوي لوك على البمفر",
        'locked'     => "موسوي لوك",
        'deleted'    => "ممسوحة",
        'history'    => "View author history",

        'catalog_stats' => "R: :reply_count / F: :file_count / P: :page",

        // Translator's Note:
        // This is a bit silly. It just means the poster
        // found the site via "Adventure!" mode. This can be
        // translated to anything else.
        'adventurer' => ".جى من القمر",
    ],

    // Post Actions
    'action'            => [
        'view'              => "إفتح",    // Open a thread in the catalog
        'open'              => "Actions", // List of actions
        'close'             => "Close",

        'ban'               => "Ban",
        'ban_delete'        => "Ban &amp; Delete",
        'ban_delete_board'  => "Ban &amp; Delete Board-wide",
        'ban_delete_global' => "Ban &amp; Delete Site-wide",
        'bumplock'          => "Bumplock",
        'unbumplock'        => "Un-Bumplock",
        'delete'            => "Delete",
        'delete_board'      => "Delete Board-wide",
        'delete_global'     => "Delete Site-wide",
        'feature_global'    => "Feature Site-wide",
        'lock'              => "Lock",
        'unlock'            => "Unlock",
        'edit'              => "Edit",
        'sticky'            => "Sticky",
        'unsticky'          => "Unsticky",
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

    'meta'              => [
        'banned'            => "User was banned for this post.",
        'banned_for'        => "User was banned for this post. Reason: <em>:reason</em>",
        'updated_by'        => "This post was last edited by :name at :time.",
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
            'replies' => "{0}unknown posts|{1}:count post|[2,Inf]:count posts",
            'files'   => "{0}unknown files|{1}:count file|[2,Inf]:count files",
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
        'index'   => "أم البورد",
        'catalog' => "إقامة",
        'logs'    => "مسجلات البورد",
        'gotop'   => "روح فق",
        'gobot'   => "روح تحت",

        'reply'   => "عمل ريبلاي",
        'thread'  => "سوي ثريد جديد",
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
    'sfw'      => "حلال",
    'nsfw'     => "حرام",


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
    'overboard'  => "مصر أم الدنيا",
    'multiboard' => "مدري شو",

    'landing'    => [
        'thread_submitted' => "تعجبني يا خلفان",
        'reply_submitted'  => "ربلاي ممتاز",
    ],
];
