<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Moderator log files
    |--------------------------------------------------------------------------
    |
    | File translations are used to populate the content of moderator log tables.
    | These arrays should correspond to the `action_name` field of `logs`.
    |
    */

    'board' => [
        'create' => "Board created.",
    ],

    'role' => [
        'add'        => "Added caste <span class=\"role\">:caste</span>",

        'permission' => [
            'modified' => "Modified <span class=\"role\">:role</span> permissions.",
        ],
    ],

    'attachment' => [
        'delete' => "Deleted attachment on post <a class=\"post\" href=\"/:board_uri/post/:board_id\">&gt;&gt;:board_id</a>.",
        'spoiler' => "Spoiled attachment on post <a class=\"post\" href=\"/:board_uri/post/:board_id\">&gt;&gt;:board_id</a>.",
        'unspoiler' => "Un-spoiled attachment on post <a class=\"post\" href=\"/:board_uri/post/:board_id\">&gt;&gt;:board_id</a>.",
    ],

    'post' => [
        'ban' => [
            'local' => "Banned <span class=\"ip\">:ip</span> from /:board_uri/ for :time. Reason: \"<em class=\"ban-reason\">:justification</em>\".",
            'global' => "Banned <span class=\"ip\">:ip</span> <strong class=\"ban-globally\">globally</strong> for :time. Reason: \"<em class=\"ban-reason\">:justification</em>\".",
        ],

        'capcode'  => "Created staff post <a class=\"post\" href=\"/:board_uri/thread/:board_id\">&gt;&gt;:board_id</a> as <span class=\"capcode capcode-:role\">:capcode</a>.",

        'delete'   => [
            'batch' => "Deleted :posts post(s) for <span class=\"ip\">:ip</span> in /:board_uri/.",
            'op' => "Deleted thread <span class=\"quote\">&gt;&gt;:board_id</span> with :replies reply(s).",
            'reply' => "Deleted post <span class=\"quote\">&gt;&gt;:board_id</span> that was in response to <a class=\"post\" href=\"/:board_uri/thread/:op_id\">&gt;&gt;:op_id</a>.",
        ],

        'edit' => "Edited <a class=\"post\" href=\"/:board_uri/thread/:board_id\">&gt;&gt;:board_id</a>.",
        'sticky' => "Stickied <a class=\"post\" href=\"/:board_uri/thread/:board_id\">&gt;&gt;:board_id</a>.",
        'unsticky' => "Unstickied <a class=\"post\" href=\"/:board_uri/thread/:board_id\">&gt;&gt;:board_id</a>.",
    ],
];
