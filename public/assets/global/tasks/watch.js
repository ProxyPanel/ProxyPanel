import gulp from 'gulp';
import config from '../config';

// WATCH TASKS
// ------------------
// watches for changes, recompiles & injects html + assets
gulp.task('watch:styles', () => {
  gulp.watch(`${config.styles.source}/**/*.scss`, gulp.series('styles'));
});

gulp.task('watch:scripts', () => {
  gulp.watch(`${config.scripts.source}/**/*.js`, gulp.series('scripts'));
});

gulp.task('watch:images', () => {
  gulp.watch(`${config.images.source}/**/*`, gulp.series('images'));
});

gulp.task('watch:vendor', () => {
  gulp.watch(`${config.vendor.source}/**/*`, gulp.series('vendor:styles'));
});

gulp.task('watch:fonts', () => {
  gulp.watch(`${config.fonts.source}/**/*`, gulp.series('fonts'));
});

gulp.task('watch:misc', () => {
  gulp.watch(
    ['config.js'],
    gulp.series('styles', 'scripts')
  );
});

gulp.task(
  'watch',
  gulp.parallel(
    'watch:styles',
    'watch:scripts',
    'watch:images',
    'watch:vendor',
    'watch:fonts',
    'watch:misc'
  )
);
