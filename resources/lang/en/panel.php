<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Panel
    |--------------------------------------------------------------------------
    |
    | Generic control panel lines.
    |
    */

    'authed_as' => "Hello, :name",

    'approve'   => "Approve",
    'reject'    => "Reject",

    'error' => [
        'auth'  => [
            'csrf_token' => "The control panel requires cookies to be enabled.",
        ],
        'board' => [
            'create_more_than_max' => "{0,1}You may not create more than 1 board.|[2,Inf]You may not create more than :boardCreateMax boards.",
            'create_so_soon'       => "{0,1}You must wait 1 minute before creating another board.|[2,Inf]You must wait :boardCreateTimer minutes before creating another board.",
        ],
        'staff' => [
            'no_roles' => "You must create an assignable role before you can add staff.",
        ],
    ],

    'title' => [
        'appeals'            => "Ban Appeals",
        'board'              => "Configuration for /:board_uri/",
        'site'               => "Site Configuration",
        'bans_public'        => "Public Bans",
        'board_create'       => "Create a Board",
        'board_create_your'  => "Create your Board",
        'board_role_add'     => "New Role for /:board_uri/",
        'board_role_edit'    => "Edit :role for /:board_uri/",
        'board_role_list'    => "Roles for /:board_uri/",
        'board_role_delete'  => "Delete Role",
        'board_staff_list'   => "Staff of /:board_uri/",
        'board_staff_add'    => "Creating /:board_uri/ Staff",
        'board_staff_edit'   => "Editing /:board_uri/ Staff: :staff_name",
        'board_staff_delete' => "Removing /:board_uri/ Staff: :staff_name",
        'permissions'        => ":role Role Permissions",
        'they_are_banned'    => "Ban Overview",
        'you_are_banned'     => "You are BANNED!",
        'you_are_not_banned' => "You are not banned.",
        'reports'            => "Open Reports",

        'page'               => "Static Page",
        'page_create'        => "Create Static Page",
        'page_update'        => "Update Statc Page",
        'board_pages'        => "Static Pages for /:board_uri/",
        'site_pages'         => "Static Global Pages",
        'feature_board'      => "Feature Board",
    ],

    'action'    => [
        'edit'                 => "Edit",
        'update'               => "Update",

        'add_staff'            => "Add Staff",
        'edit_staff'           => "Save Staff",
        'delete_staff'         => "Remove Staff",

        'add_role'             => "Create Role",
        'edit_role'            => "Save Role",
        'delete_role'          => "Delete Role",

        'create_page'          => "Create Page",
        'delete_page'          => "Delete Page",
        'update_page'          => "Update Page",
        'view_page'            => "View Page",

        'feature'              => "Feature Now",
        'unfeature'            => "Unfeature",
    ],

    'field'     => [
        'email'                => "E-Mail Address",
        'login'                => "Login",
        'logout'               => "Logout",
        'password'             => "Password",
        'password_confirm'     => "Confirm Password",
        'password_current'     => "Current Password",
        'password_new'         => "New Password",
        'password_new_confirm' => "Confirm New Password",
        'remember'             => "Remember Me",
        'uid'                  => "Username or Email",
        'username'             => "Username",
        'register'             => "Register",

        'assets_count'         => "{0}No assets|{1}1 custom asset|[2,Inf]:count custom assets",
        'staff_count'          => "{0}No staff|{1}1 staff member|[2,Inf]:count staff members",

        'login_link'           => [
            'password_forgot'      => "Forgot Password",
            'register'             => "Register an account",
        ],

        'desc'                 => [
            'email' => "This field is optional, but is required to reset your password.",
        ],
    ],

    'confirm' => [
        'feature' => "Featuring a thread will update its \"Featured At\" column to be the current date.<br />" .
            "Unfeaturing a thread will clear that value.<br />" .
            "Featured boards on the front page are sorted by their Featured At date.",

        'delete_staff_self' => "<strong>Warning!</strong><br />" .
            "You are <em>removing yourself</em> from this board's staff.<br />" .
            "You may not be able to regain control later on.",

        // Logical switch, not plural.
        'featured_at' => "{0}Board not featured.|[1,Inf]Board last featured :featured_at",
    ],

    'list'      => [
        'head'      => [
            'staff'         => "Staff",
            'pages'         => "Pages",
            'roles'         => "Roles",
            'users'         => "Users",

            'global_roles'  => "Site-Wide Roles",
            'board_roles'   => "Board-Specific Roles",
        ],

        'field'     => [
            'permissions'   => "Permissions",
            'userinfo'      => "User Info",
        ],
    ],

    'staff'     => [
        'select_existing_form' => "Add existing account as staff",
        'select_register_form' => "Register new account for staff member",
    ],

    'appeals'   => [
        'empty' => "There are no pending appeals.",
    ],

    'bans'      => [
        'ban_list_empty' => "No active bans for your IP address can be found on any board.",

        'ban_list_desc' => "<p>This is a list of bans applied to your IP address. " .
                        "You may be affected by a ban not intended for you, especially if on a public computer, network, or using a VPN, Proxy, or Tor. " .
                        "Some bans are applied to entire ranges and will be denoted with a <a href=\"https://en.wikipedia.org/wiki/Classless_Inter-Domain_Routing\">CIDR integer</a>.</p>" .
                        "<p>Sometimes, you may appeal a ban to the staff responsible for it. " .
                        "If you can, a link will be visible in the ban row that goes to the appeals page.</p>",

        'table' => [
            'board'          => "Banned In",
            'ban_ip'         => "Banned IP",
            'ban_appeal'     => "Appeal Status",
            'ban_user'       => "Moderator",
            'ban_placed'     => "Placed On",  // Placed On .. 19.12.2015
            'ban_expire'     => "Expires At", // Expires At .. 19.12.2015
            'ban_placed_ago' => "Placed",     // Placed .. 3 hours ago
            'ban_expire_in'  => "Expires In", // Expires In .. 3 hours from now
            'appeal_text'    => "Appeal",
        ],

        'ban_global'     => "All Boards",

        'appeal_open'    => "Appeals Open",
        'appeal_closed'  => "Appeals Closed",
        'appeal_expired' => "Ban has Expired",

        'ban_review' => [
            'expired'        => "This ban is expired.",
            'seeing'         => "Now that you have seen this ban you can continue posting.",

            'banned_from'    => "Banned from /:board_uri/.",
            'banned_all'     => "Banned from <strong>all boards</strong>.",
            'no_reason'      => "No reason given.",
            'expires_at'     => "This ban was placed at :start and expires at :end, which is :diff.",
            'expires_no'     => "This ban was placed at :start and <strong>never</strong> expires.",
            'mod'            => "The volunteer who filed your ban was :mod.",
            'identity_match' => "According to our server, your IP is <tt>:ip</tt> and is affected by this ban.",
            'identity_notit' => "According to our server, your IP is <tt>:ip</tt> and is <em>not</em> affected by this ban.",
            'appeal_now'     => "You may appeal your ban.",
            'appeal_pending' => "Your appeal was submitted on :date (:diff) and is pending approval.",
            'appeal_at'      => "Your ban is too short to appeal and must be waited out.",
            'appeal_no'      => "Your appeal has been reviewed and denied.",
            'appeal_yes'     => "Your appeal has been reviewed and granted.",

            'appeal_submit'  => "Submit Appeal",
        ],
    ],

    'reports'   => [
        'empty'          => "You have no pending reports to review.",
        'dismisssed'     => "{1}Report dismissed.|[2,Inf]Dismissed :reports reports.",
        'demoted'       => "{1}Report demoted.|[2,Inf]Demoted :reports reports.",
        'promoted'       => "{1}Report promoted.|[2,Inf]Promoted :reports reports.",

        'is_not_associated' => "Anonymous Report",
        'is_associated'  => "Authored Report",

        'dismiss_post'   => "Dismiss Post",
        'dismiss_ip'     => "Dismiss IP",
        'dismiss_single' => "Dismiss",

        'promote_post'   => "Promote Post",
        'promote_single' => "Promote",

        'demote_post'    => "Demote Post",
        'demote_single'  => "Demote",

        'local_single'   => "Local Report",
        'global_single'  => "Global Report",
    ],

    'adventure' => [
        'go'  => "ADVENTURE!",
        'sad' => "There's no where to go for an adventure. :(",
    ],

    'password'  => [
        'reset' => "Reset Password",
        'user'  => "No user can be found with that email address.",
        'sent' => "Your password reset reqest has been sent to your email.",
        'password_old' => "You entered an incorrect current password.",
        'reset_success' => "Your password has been reset.",
    ],
];
