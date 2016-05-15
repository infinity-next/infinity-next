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
        ], 'public/static/builds/vendor.css')

        // Global (boards + normative)
        .sass([
            'global.scss'
        ], 'public/static/builds/global.css')

        // Public (no account required, not panel)
        .sass([
            'public.scss'
        ], 'public/static/builds/public.css')

        // Panel (/cp/ only)
        .sass([
            'panel.scss'
        ], 'public/static/builds/panel.css')

        // Publishing
        .version([
            "static/builds/vendor.css",
            "static/builds/global.css",
            "static/builds/public.css",
            "static/builds/panel.css"
        ]);
});
