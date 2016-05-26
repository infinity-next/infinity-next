<?php

return [
    'title' => [
        'welcome'       => ":site_name أهلن بكى إلى",
        'statistics'    => "إحصائيات الموقع",

        'featured_post' => "المنشور المعلقة",
        'recent_images' => "الصور الأخيرة",
        'recent_posts'  => "المنشورات الأخيرة",
    ],

    'warning' => "تحذير: بعض لوحة إعلانات على هذا الموقع ربما يحتوي على اشياء قد تكون لالكبار أو يمكن أن تكون مهين (قد يكون حراما). <wbr />" .
        "لا تستخدم هذا الموقع إذا كان غير قانوني عليك لعرض هذه الأشياء.<wbr />" .
        "بعض لوحة على هذا الموقع تم إنشاؤها كامله من المستخدمين ولا تمثل آراء إدارة إنفينيتي.<wbr />" .
        "لمصلحة حرية التعبير،فقط يتم حذف الأشياء التي تنتهك DMCA (القانون الأمريكي).<wbr />",

    'info' => [
        'welcome' => "<p><a href=\"https://github.com/infinity-next/infinity-next\">انفنتي نكست</a> هذا الموقع يستخدم" .
            ".<a href=\"https://laravel.com\">الإطار لارافل</a> مع PHP لوحات صور " .
            " يمكن لأي شخص تحميل والإعداد مثيل إنفينيتي نكست من تلقاء نفسها ،AGPL 3.0 مرخص تحت </p>",

        'statistic' => [
            // These items are pluralized first, then submitted as the board and post strings to the below definitions.
            'post_count' => "{1}<strong>:posts</strong> post|[0,Inf]<strong>:posts</strong> posts",
            'board_count' => "{1}<strong>:boards</strong> board|[0,Inf]<strong>:boards</strong> boards",

            // {1} if there is only 1 board, the rest if there are >1 board.
            'boards' => "{1}هناك لوحة واحد|[0,Inf] جمع :boards_total و عامة :boards_public الأن يوجد هناك",
            'posts'  => "Site-wide, :recent_posts have been made in the last day.",
            'posts_all' => ":posts_total هي :start_date محنووات قدمت من",
        ],
    ]
];
