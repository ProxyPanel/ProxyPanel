import gulp from 'gulp';
import config from '../config';
import gulpif from 'gulp-if';
import notify from 'gulp-notify';
import imagemin from 'gulp-imagemin';
import pngquant from 'imagemin-pngquant';
import size from 'gulp-size';
import changed from 'gulp-changed';
import plumber from 'gulp-plumber';
import notifier from 'node-notifier';
import del from 'del';

// IMAGES
// ------------------
// compressing images (unless they already got compressed)
gulp.task('images', () => {
  return gulp
    .src(`${config.images.source}/**/*.+(png|jpg|jpeg|gif|svg)`)
    .pipe(changed(`${config.images.build}`))
    .pipe(
      plumber({errorHandler: notify.onError('Error: <%= error.message %>')})
    )
    .pipe(
      imagemin({
        progressive: true,
        use: [pngquant()],
      })
    )
    .pipe(size({showFiles: true}))
    .pipe(plumber.stop())
    .pipe(gulp.dest(`${config.images.build}`))
    .pipe(
      gulpif(
        config.enable.notify,
        notify({
          title: config.notify.title,
          message: 'Images task complete',
          onLast: true,
        })
      )
    );
});

// Clean images files
gulp.task('clean:images', (done) => {
  return del([`${config.images.build}/**/*`]).then(() => {
    if (config.enable.notify) {
      notifier.notify({
        title: config.notify.title,
        message: 'Clean images task complete',
      });
    }

    done();
  });
});
