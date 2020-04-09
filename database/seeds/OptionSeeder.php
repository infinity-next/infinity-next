<?php

use Illuminate\Database\Seeder;

use App\Option;

class OptionSeeder extends Seeder {

    public function run()
    {
        $this->command->info('Seeding system options.');

        $option_count = Option::count();

        foreach ($this->slugs() as $slugType => $slugs) {
            foreach ($slugs as $slug) {
                $slug['option_type'] = $slugType;

                if (!isset($slug['format_parameters']) || is_null($slug['format_parameters'])) {
                    $slug['format_parameters'] = "{}";
                }

                $option = Option::updateOrCreate([
                    'option_name' => $slug['option_name'],
                ], $slug);

                // Insert a default site setting.
                if ($option->wasRecentlyCreated && $slugType == "site") {
                    $option->siteSetting()->create([
                        'option_name'  => $slug['option_name'],
                        'option_value' => $slug['default_value'],
                    ]);
                }
            }
        }

        $option_count = Option::count() - $option_count;

        $this->command->info("Done. Seeded {$option_count} new permission(s).");
    }

    private function slugs()
    {
        return [
            'site' => [
                [
                    'option_name'           => "adventureEnabled",
                    'default_value'         => false,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],
                [
                    'option_name'           => "adventureIcons",
                    'default_value'         => true,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],

                [
                    'option_name'           => "attachmentFilesize",
                    'default_value'         => "1024",
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min",
                ],
                [
                    'option_name'           => "attachmentThumbnailSize",
                    'default_value'         => "250",
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 50 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min",
                ],
                [
                    'option_name'           => "attachmentThumbnailQuality",
                    'default_value'         => "75",
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 100 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min",
                ],
                [
                    'option_name'           => "attachmentThumbnailJpeg",
                    'default_value'         => 0,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => 'boolean'
                ],
                [
                    'option_name'           => "attachmentName",
                    'default_value'         => "%t-%i",
                    'data_type'             => "string",
                    'validation_parameters' => "required|string|min:\$min",
                ],

                [
                    'option_name'           => "banMaxLength",
                    'default_value'         => "30",
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => -1 ] ),
                    'data_type'             => "integer",
                    'validation_parameters' => "required|min:\$min",
                ],
                [
                    'option_name'           => "banSubnets",
                    'default_value'         => 1,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => 'boolean'
                ],

                [
                    'option_name'           => "boardCreateMax",
                    'default_value'         => 0,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min",
                ],
                [
                    'option_name'           => "boardCreateTimer",
                    'default_value'         => 15,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min",
                ],
                [
                    'option_name'           => "boardListShow",
                    'default_value'         => 1,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],
                [
                    'option_name'           => "boardUriBanned",
                    'default_value'         => "",
                    'format'                => "textbox",
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|string",
                ],

                [
                    'option_name'           => "attachmentThumbnailJpeg",
                    'default_value'         => 0,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => 'boolean'
                ],

                [
                    'option_name'           => "canary",
                    'default_value'         => false,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],

                [
                    'option_name'           => "captchaEnabled",
                    'default_value'         => false,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],
                [
                    'option_name'           => "captchaLifespanTime",
                    'default_value'         => 60,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min",
                ],
                [
                    'option_name'           => "captchaLifespanPosts",
                    'default_value'         => 10,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min",
                ],

                [
                    'option_name'           => "postFloodTime",
                    'default_value'         => 5,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min",
                ],
                [
                    'option_name'           => "threadFloodTime",
                    'default_value'         => 30,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min",
                ],

                [
                    'option_name'           => "globalReportText",
                    'default_value'         => "",
                    'format'                => "textbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|min:\$min|max:\$max",
                ],

                [
                    'option_name'           => "ephePostIpLife",
                    'default_value'         => 7,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min",
                ],
                [
                    'option_name'           => "ephePostHardDelete",
                    'default_value'         => 7,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min",
                ],
                [
                    'option_name'           => "epheMediaPrune",
                    'default_value'         => 31,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min",
                ],


                [
                    'option_name'           => "copyrightAddendum",
                    'default_value'         => "",
                    'format'                => "textbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "siteAnnouncement",
                    'default_value'         => "",
                    'format'                => "textbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "siteDescription",
                    'default_value'         => "",
                    'format'                => "textbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "siteName",
                    'default_value'         => "Infinity Next",
                    'format'                => "text",
                    'format_parameters'     => json_encode( [ 'min' => 1, 'max' => 64, ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
            ],

            'board' => [
                [
                    'option_name'           => "boardCustomCSSEnable",
                    'default_value'         => false,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],
                [
                    'option_name'           => "boardCustomCSSSteal",
                    'default_value'         => "",
                    'format'                => "text",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 32 ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "boardCustomCSS",
                    'default_value'         => "",
                    'format'                => "textbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|min:\$min|max:\$max|css",
                ],
                [
                    'option_name'           => "boardLanguage",
                    'default_value'         => config('app.locale'),
                    'format'                => "template",
                    'format_parameters'     => json_encode( [ 'lang' => implode(",", array_keys(trans('lang'))) ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|string|in:\$lang",
                ],
                [
                    'option_name'           => "boardReportText",
                    'default_value'         => "",
                    'format'                => "textbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65535 ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "boardBacklinksCrossboard",
                    'default_value'         => true,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],
                [
                    'option_name'           => "boardBacklinksBlacklist",
                    'default_value'         => "",
                    'format'                => "textbox",
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|string",
                ],
                [
                    'option_name'           => "boardBacklinksWhitelist",
                    'default_value'         => "",
                    'format'                => "textbox",
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|string",
                ],

                [
                    'option_name'           => "postAnonymousName",
                    'default_value'         => null,
                    'format'                => "text",
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|string"
                ],
                [
                    'option_name'           => "postAttachmentsMax",
                    'default_value'         => 5,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 10 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min|max:\$max|greater_than:postAttachmentsMin",
                ],
                [
                    'option_name'           => "postAttachmentsMin",
                    'default_value'         => 0,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 10 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "postMaxLength",
                    'default_value'         => 2048,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65534 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "integer|min:\$min|max:\$max|greater_than:postMinLength",
                ],
                [
                    'option_name'           => "postMinLength",
                    'default_value'         => 0,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 65534 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "integer|min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "postNewLines",
                    'default_value'         => 50,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "integer|min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "postsPerPage",
                    'default_value'         => "10",
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 5, 'max' => 20 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "postsThreadId",
                    'default_value'         => false,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],
                [
                    'option_name'           => "postsAuthorCountry",
                    'default_value'         => false,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],
                [
                    'option_name'           => "postsAllowAuthor",
                    'default_value'         => true,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],
                [
                    'option_name'           => "postsAllowSubject",
                    'default_value'         => true,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],


                [
                    'option_name'           => "threadAttachmentsMin",
                    'default_value'         => 1,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 10 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "required|min:\$min|max:\$max|less_than:postAttachmentsMax",
                ],
                [
                    'option_name'           => "threadRequireSubject",
                    'default_value'         => false,
                    'format'                => "onoff",
                    'data_type'             => "boolean",
                    'validation_parameters' => "boolean",
                ],

                [
                    'option_name'           => "epheSageThreadReply",
                    'default_value'         => 350,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "epheSageThreadDays",
                    'default_value'         => 0,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 365 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "epheSageThreadPage",
                    'default_value'         => 0,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "epheLockThreadReply",
                    'default_value'         => 700,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "epheLockThreadDays",
                    'default_value'         => 0,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 365 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "epheLockThreadPage",
                    'default_value'         => 0,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "epheDeleteThreadReply",
                    'default_value'         => 0,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "epheDeleteThreadDays",
                    'default_value'         => 0,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 365 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],
                [
                    'option_name'           => "epheDeleteThreadPage",
                    'default_value'         => 16,
                    'format'                => "spinbox",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 1000 ] ),
                    'data_type'             => "unsigned_integer",
                    'validation_parameters' => "min:\$min|max:\$max",
                ],


                [
                    'option_name'           => "originalityImages",
                    'default_value'         => "",
                    'format'                => "select",
                    'format_parameters'     => json_encode( [ 'choices' => [ 'thread', 'board' ] ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|string|in:\$choices",
                ],
                [
                    'option_name'           => "originalityPosts",
                    'default_value'         => "",
                    'format'                => "select",
                    'format_parameters'     => json_encode( [ 'choices' => [ 'board', 'site', 'boardr9k', 'siter9k' ] ] ),
                    'data_type'             => "string",
                    'validation_parameters' => "nullable|string|in:\$choices",
                ],

                [
                    'option_name'           => "boardWordFilter",
                    'default_value'         => "",
                    'format'                => "template",
                    'format_parameters'     => json_encode( [ 'min' => 0, 'max' => 50 ] ),
                    'data_type'             => "array",
                    'validation_parameters' => "nullable|array|min:\$min|max:\$max",
                ],

            ],
        ];
    }
}


use App\OptionGroup;
use App\OptionGroupAssignment;

class OptionGroupSeeder extends Seeder {

    public function run()
    {
        $this->command->info('Seeding option groups and relationships.');

        OptionGroupAssignment::truncate();

        foreach ($this->slugs() as $slug) {
            $optionGroupOptions = $slug['options'];
            unset($slug['options']);

            $optionGroup = OptionGroup::firstOrNew([
                'group_name' => $slug['group_name'],
            ]);

            $optionGroup->debug_only = $slug['debug_only'];
            $optionGroup->display_order = $slug['display_order'];

            $optionGroup->save();

            foreach ($optionGroupOptions as $optionGroupIndex => $optionGroupOption) {
                $optionGroupOptionModel = $optionGroup
                    ->assignments()
                    ->firstOrNew([
                        'option_name' => $optionGroupOption,
                    ]);

                $optionGroupOptionModel->display_order = $optionGroupIndex * 10;
                $optionGroupOptionModel->save();

                $optionGroupOptionModels[] = $optionGroupOptionModel;
            }
        }
    }

    private function slugs()
    {
        return [
            [
                'group_name'    => "site",
                'debug_only'    => false,
                'display_order' => 0,

                'options' => [
                    'siteName',
                    'siteDescription',
                    'siteAnnouncement',
                    'copyrightAddendum',
                    'canary',
                ],
            ],

            [
                'group_name'    => "attachments",
                'debug_only'    => false,
                'display_order' => 100,

                'options'       => [
                    "attachmentFilesize",
                    "attachmentThumbnailSize",
                    "attachmentThumbnailQuality",
                    "attachmentThumbnailJpeg",
                    "attachmentName",
                ],
            ],
            [
                'group_name'    => "bans",
                'debug_only'    => false,
                'display_order' => 200,

                'options'       => [
                    "banMaxLength",
                    "banSubnets",
                ],
            ],
            [
                'group_name'    => "boards",
                'debug_only'    => false,
                'display_order' => 300,

                'options'       => [
                    "boardCreateMax",
                    "boardCreateTimer",
                    "boardUriBanned",
                ],
            ],
            [
                'group_name'    => "board_language",
                'debug_only'    => false,
                'display_order' => 250,

                'options'       => [
                    "boardLanguage",
                ],
            ],
            [
                'group_name'    => "board_ephemerality",
                'debug_only'    => false,
                'display_order' => 300,

                'options'       => [
                    // Board Settings
                    "epheSageThreadReply",
                    "epheSageThreadDays",
                    "epheSageThreadPage",
                    "epheLockThreadReply",
                    "epheLockThreadDays",
                    "epheLockThreadPage",
                    "epheDeleteThreadReply",
                    "epheDeleteThreadDays",
                    "epheDeleteThreadPage",

                    // Site Settings
                    "ephePostIpLife",
                    "ephePostHardDelete",
                    "epheMediaPrune",
                ],
            ],
            [
                'group_name'    => "board_originality",
                'debug_only'    => false,
                'display_order' => 310,

                'options'       => [
                    "originalityImages",
                    "originalityPosts",
                ],
            ],
            [
                'group_name'    => "adventures",
                'debug_only'    => false,
                'display_order' => 350,

                'options'       => [
                    "adventureEnabled",
                    "adventureIcons",
                ],
            ],
            [
                'group_name'    => "board_posts",
                'debug_only'    => false,
                'display_order' => 400,

                'options'       => [
                    "postAttachmentsMax",
                    "postAttachmentsMin",
                    "threadAttachmentsMin",
                    "postMaxLength",
                    "postMinLength",
                    "postNewLines",
                    "postFloodTime",
                    "threadFloodTime",
                    "postAnonymousName",
                    "postsAllowAuthor",
                    "postsAllowSubject",
                    "boardWordFilter",
                ],
            ],
            [
                'group_name'    => "board_threads",
                'debug_only'    => false,
                'display_order' => 500,

                'options'       => [
                    "threadRequireSubject",
                    "postsAuthorCountry",
                    "postsThreadId",
                    "postsPerPage",
                ],
            ],
            [
                'group_name'    => "captcha",
                'debug_only'    => false,
                'display_order' => 550,

                'options'       => [
                    "captchaEnabled",
                    "captchaLifespanPosts",
                    "captchaLifespanTime",
                ],
            ],
            [
                'group_name'    => "navigation",
                'debug_only'    => false,
                'display_order' => 600,

                'options'       => [
                    "boardListShow",
                ],
            ],
            [
                'group_name'    => "style",
                'debug_only'    => false,
                'display_order' => 1000,

                'options'       => [
                    "boardCustomCSSEnable",
                    "boardCustomCSSSteal",
                    "boardCustomCSS",
                ],
            ],
            [
                'group_name'    => "reports",
                'debug_only'    => false,
                'display_order' => 1200,

                'options'       => [
                    "boardReportText",
                    "globalReportText",
                ]
            ],
            [
                'group_name'    => "board_diplomacy",
                'debug_only'    => false,
                'display_order' => 1500,

                'options'       => [
                    "boardBacklinksCrossboard",
                    "boardBacklinksBlacklist",
                    "boardBacklinksWhitelist",
                ],
            ],
        ];
    }
}
