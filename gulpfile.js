process.env.DISABLE_NOTIFIER = false;

var elixir = require(
    'laravel-elixir',
    'unsemantic',
    'react'
);

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

elixir(function(mix) {
    mix
        // Vendor files
        .sass([
            '../../../node_modules/unsemantic/assets/sass/unsemantic-grid-responsive-tablet.scss'
        ], 'public/static/css/vendor.css')

        // Global (boards + normative)
        .sass([
            'global.scss'
        ], 'public/static/css/global.css')

        // Public (no account required, not panel)
        .sass([
            'public.scss'
        ], 'public/static/css/public.css')

        // Panel (/cp/ only)
        .sass([
            'panel.scss'
        ], 'public/static/css/panel.css')

        .scripts([
            'vendor/**/*.js',
            'plugins/**/*.js',
            'app/**/*.js'
        ], 'public/static/js/app.js')

        // Publishing
        .version([
            "public/static/css/vendor.css",
            "public/static/css/global.css",
            "public/static/css/public.css",
            "public/static/css/panel.css",
            "public/static/js/app.js"
        ]);
});
