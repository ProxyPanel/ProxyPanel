const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

//css bundle
mix.styles([
  'public/assets/css/bootstrap.min.css',
  'public/assets/css/bootstrap-ext  end.min.css',
  'public/assets/css/site.min.css',
  'public/assets/global/vendor/animsition/animsition.min.css',
  'public/assets/global/vendor/asscrollable/asScrollable.min.css',
  'public/assets/global/vendor/slidepanel/slidePanel.min.css',
], 'public/assets/bundle/app.min.css').options({
  processCssUrls: false,
});
