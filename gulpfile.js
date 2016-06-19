process.env.DISABLE_NOTIFIER = false;

var elixir = require('laravel-elixir');

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
        .copy(
            'node_modules/font-awesome/fonts',
            'public/static/fonts/font-awesome'
        )
        .sass([
            'vendor.scss'
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
            '../../../node_modules/jquery/dist/jquery.js',
            '../vendor/**/*.js',
            'plugins/**/*.js'
        ], 'public/static/js/vendor.js')

        .scripts([
            'app/**/*.js'
        ], 'public/static/js/app.js')

        // Publishing
        .version([
            "public/static/css/vendor.css",
            "public/static/css/global.css",
            "public/static/css/public.css",
            "public/static/css/panel.css",
            "public/static/js/vendor.js",
            "public/static/js/app.js"
        ])

        ;
});
