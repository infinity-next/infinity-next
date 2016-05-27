<?php

return [
    'title' => [
        'welcome'       => "site_name: أهلن بكى إلى",
        'statistics'    => "إحصائيات الموقع",

        'featured_post' => "المنشور المعلقة",
        'recent_images' => "الصور الأخيرة",
        'recent_posts'  => "المنشورات الأخيرة",
    ],

    'warning' => " <wbr /> تحذير: بعض لوحة إعلانات على هذا الموقع ربما يحتوي على اشياء قد تكون لالكبار أو يمكن" .
        "لا تستخدم هذا الموقع إذا كان غير قانوني عليك لعرض هذه الأشياء.<wbr />" .
        "بعض لوحة على هذا الموقع تم إنشاؤها كامله من المستخدمين ولا تمثل آراء إدارة إنفينيتي.<wbr />" .
        "لمصلحة حرية التعبير،فقط يتم حذف الأشياء التي تنتهك DMCA (القانون الأمريكي).<wbr />",

    'info' => [
        'welcome' => "<p><a href=\"https://github.com/infinity-next/infinity-next\">انفنتي نكست</a> هذا الموقع يستخدم" .
            ".<a href=\"https://laravel.com\">الإطار لارافل</a> مع PHP لوحات صور" .
            ".AGPL 3.0 مرخص تحت</p>" .

        'statistic' => [
            // These items are pluralized first, then submitted as the board and post strings to the below definitions.
            'post_count' => "{1}<strong>posts:</strong> محنووات|[0,Inf]<strong>posts:</strong> محنووات",
            'board_count' => "{1}<strong>boards:</strong> لوحة|[0,Inf]<strong>boards:</strong> لوحة",

            // {1} if there is only 1 board, the rest if there are >1 board.
            'boards' => ".جمع boards_total: عامة و boards_public: الأن يوجد هناك",
            'posts'  => ".محنووات recent_post: في الموقع كامل، تم انشا",
            'posts_all' => "start_date: هي محنووات قدمت من posts_total:",
        ],
    ]
];
