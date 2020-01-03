import gulp from 'gulp';
import config from '../config';
import gulpif from 'gulp-if';
import notify from 'gulp-notify';

// Copy assets to dist on build task
gulp.task('copy', () => {
  return gulp
    .src(`${config.assets.source}/**/*`)
    .pipe(gulp.dest(`${config.assets.build}`))
    .pipe(
      gulpif(
        config.enable.notify,
        notify({
          title: config.notify.title,
          message: 'Copy task complete',
          onLast: true,
        })
      )
    );
});
