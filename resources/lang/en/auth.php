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

    "captcha"  => [
        "lifespan" => "{0} You must solve a Captcha every post."
            . "|{1} You must solve a Captcha every other post."
            . "|[2,*] You must solve a Captcha every :value posts.",
        "unaccountable" => "You are using Tor and must always solve a Captcha.",
    ],

    "post"     => [
        "cannot_ban_without_ip" => "This post has no IP to ban.",
        "cannot_edit_capcode" => "You cannot change the capcode on a post.",
        "cannot_reply" => "You cannot post replies.",
        "only_on_an_op" => "This action is valid only on the first post of a thread.",
        "thread_is_locked" => "This thread is locked.",
    ],
];
