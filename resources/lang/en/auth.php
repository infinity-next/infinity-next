<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | Any text regarding registering, signing into, or recovering an account
    | belongs here.
    |
    */

    "password" => "Passwords must be at least six characters and match the confirmation.",
    "user"     => "We can't find a user with that e-mail address.",
    "token"    => "This password reset token is invalid.",
    "sent"     => "We have e-mailed your password reset link!",
    "reset"    => "Your password has been reset!",
    "mismatch" => "These credentials do not match our records.",

    "board" => [
        "cannot_edit_uri" => "You cannot change this board's name.",
        "cannot_view_history" => "You cannot view post history on this board.",
    ],

    "captcha" => [
        "lifespan" => "{0} You must solve a Captcha every post."
            . "|{1} You must solve a Captcha every other post."
            . "|[2,*] You must solve a Captcha every :value posts.",
        "unaccountable" => "You are using Tor and must always solve a Captcha.",
    ],

    "post" => [
        "cannot_edit_capcode" => "You cannot change the capcode on a post.",
        "cannot_feature" => "You cannot feature posts on the site.",
        "cannot_reply" => "You cannot post replies.",
        "cannot_sticky" => "You cannot sticky posts.",
        "cannot_view_reports" => "You cannot review reports on this board.",
        "cannot_without_password" => "You cannot dom this without the post's password.",
        "no_ip_address" => "This post has no IP address, so certain actions are impossible.",
        "only_on_an_op" => "This action is valid only on the first post of a thread.",
        "thread_is_locked" => "This thread is locked.",
    ],

    "report" => [
        "already_demoted" => "This report is already demoted.",
        "already_promoted" => "This report has already been promoted and cannot be promoted again.",
        "not_global" => "This action can only be done to global reports.",
        "not_local" => "This action can only be done to local reports.",
     ],

    "site" => [
        "cannot_admin_config" => "You cannot administrate the system config.",
        "cannot_admin_permissions" => "You cannot administrate user permissions.",
        "cannot_admin_tools" => "You cannot access administrative tools.",
        "cannot_admin_users" => "You cannot administrate users.",
        "cannot_create_report" => "You cannot report posts to board moderators.",
        "cannot_create_global" => "You cannot report posts to site administrators.",
        "cannot_upload_files" => "Your connection is not allowed to upload unrecognized files.",
        "cannot_view_history" => "You cannot view global post history.",
        "cannot_view_ip_address" => "You cannot view unredacted IP addresses.",
        "cannot_view_reports" => "You cannot view reports made to site administration.",
    ],
];
