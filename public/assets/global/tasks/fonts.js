import gulp from 'gulp';
import sass from 'gulp-sass';
import postcss from 'gulp-postcss';
import size from 'gulp-size';
import config from '../config';
import minify from 'gulp-clean-css';
import rename from 'gulp-rename';
import gulpif from 'gulp-if';
import notify from 'gulp-notify';
import del from 'del';
import notifier from 'node-notifier';

// FONTS
// ------------------
gulp.task('fonts', () => {
  return gulp
    .src(`${config.fonts.source}/*/*.scss`)
    .pipe(
      sass({
        precision: 10, // https://github.com/sass/sass/issues/1122
        includePaths: config.styles.include,
      })
    )
    .pipe(postcss())
    .pipe(size({gzip: true, showFiles: true}))
    .pipe(gulp.dest(`${config.fonts.build}`))
    .pipe(minify())
    .pipe(rename({
      extname: '.min.css'
    }))
    .pipe(size({gzip: true, showFiles: true}))
    .pipe(gulp.dest(`${config.fonts.build}`))
    .pipe(
      gulpif(
        config.enable.notify,
        notify({
          title: config.notify.title,
          message: 'Fonts task complete',
          onLast: true,
        })
      )
    );
});

// Clean fonts files
gulp.task('clean:fonts', (done) => {
  return del([`${config.fonts.build}/**/*.css`]).then(() => {
    if (config.enable.notify) {
      notifier.notify({
        title: config.notify.title,
        message: 'Clean fonts task complete',
      });
    }

    done();
  });
});
