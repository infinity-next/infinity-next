process.env.DISABLE_NOTIFIER = false;

const mix = require('laravel-mix');

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

mix
    // Vendor files
    .copy(
        'node_modules/font-awesome/fonts',
        'public/static/fonts/font-awesome'
    )
    .sass('resources/assets/sass/vendor.scss', 'public/static/css')

    // Global (boards + normative)
    .sass('resources/assets/sass/global.scss', 'public/static/css')

    // Public (no account required, not panel)
    .sass('resources/assets/sass/public.scss', 'public/static/css')

    // Panel (/cp/ only)
    .sass('resources/assets/sass/panel.scss', 'public/static/css')

    .scripts([
        '../../../node_modules/jquery/dist/jquery.js',
        '../vendor/**/*.js',
        'plugins/**/*.js'
    ], 'public/static/js/vendor.js')

    .scripts([
        'app/**/*.js'
    ], 'public/static/js/app.js')
;

if (mix.inProduction()) {
    mix.version();
}
