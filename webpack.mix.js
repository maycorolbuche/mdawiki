const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

// app.js
mix
    .js('resources/js/app.js', 'public/js');

// app.css
mix
    .postCss('resources/css/fonts/open_sans.css', 'public/css/app.css')
    .sass('resources/sass/app.scss', 'public/css')
    .postCss('resources/css/app.css', 'public/css')
    .postCss('resources/css/editor.css', 'public/css/app.css')
    .postCss('resources/css/sidebar.css', 'public/css/app.css');

// pdf.css
mix
    .sass('resources/sass/pdf.scss', 'public/css');

// components.css
mix
    .postCss('resources/css/components.css', 'public/css');

// imagens
mix
    .copyDirectory('resources/img', 'public/img');
