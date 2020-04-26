<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Navigation for all systems throughout the site.
    |
    */

    'navigation'  => "<i class=\"fas fa-bars\"></i>&nbsp;Navigation",

    'global'      => [
        'view_all_boards' => "روي كول البردات",

        'flyout'       => [
            'popular_boards'  => "Popular Boards",
            'recent_boards'   => "Recently Active Boards",
            'favorite_boards' => "Favorite Boards",
        ],

        'home'         => "مصر أم الدنيا",
        'panel'        => "Panel",
        'boards'       => "بوردات",
        'new_board'    => "إفتح بورد",
        'contribute'   => "Contribute",
        'donate'       => "Fund Us",
        'adventure'    => "Adventure",
        'options'      => "Options",

        // Translators:
        // 'Overboard' has a specific meaning to English IB users.
        // Feel free to translate to Recent Posts instead.
        'recent_posts' => "Overboard",
    ],

    'panel'       => [
        'primary' => [
            'home'     => "Home",
            'board'    => "Boards",
            'site'     => "Site",
            'users'    => "Users",
            'logout'   => "Logout",
            'register' => "Register",
            'login'    => "Login",
        ],

        'secondary' => [
            'home'   => [
                'account'         => "Account",
                'password_change' => "Change Password",

                'status'          => "Status",
                'banned'          => "Am I Banned?",
                'bans'            => "Bans",

                'sponsorship'     => "Sponsorship",
                'donate'          => "Send Cash Contribution",
            ],

            'site'   => [
                'setup'           => "Setup",
                'pages'           => "Static Pages",
                'config'          => "Config",
            ],


            'board'  => [
                'create'          => "Create a Board",

                'boards'          => "Boards",
                'assets'          => "Assets",
                'config'          => "Config",
                'staff'           => "Staff",

                'discipline'      => "Discipline",
                'appeals'         => "Appeals",
                'reports'         => "Reports",
            ],

            'users'  => [
                'permissions'     => "Permissions",
                'role_permissions' => "Role Permissions",
            ],
        ],

        'tertiary' => [
            'board_settings' => [
                'assets'  => "Assets",
                'basic'   => "Basic Details",
                'roles'   => "Roles",
                'staff'   => "Staff",
                'style'   => "Styling",
                'tags'    => "Tags",
                'pages'   => "Static Pages",
            ],
        ],
    ],
];
