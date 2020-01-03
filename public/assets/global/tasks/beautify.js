import gulp from 'gulp';
import config from '../config';
import prettier from 'gulp-nf-prettier';
import changed from 'gulp-changed';
import size from 'gulp-size';
import plumber from 'gulp-plumber';
import gulpif from 'gulp-if';
import notify from 'gulp-notify';
import postcss from 'gulp-postcss';
import syntaxScss from 'postcss-scss';
import stylefmt from 'stylefmt';

gulp.task('beautify:scripts', () => {
  return gulp
    .src(`${config.scripts.source}/**/*.js`, {
      base: './', since: gulp.lastRun('beautify:scripts'),
    })
    .pipe(changed(`${config.scripts.source}`))
    .pipe(
      plumber({errorHandler: notify.onError('Error: <%= error.message %>')})
    )
    .pipe(
      prettier({
        parser: 'flow',
        tabWidth: 2,
        useTabs: false,
        semi: true,
        singleQuote: true,
        bracketSpacing: true,
      })
    )
    .pipe(size({showFiles: true}))
    .pipe(plumber.stop())
    .pipe(gulp.dest('./'))
    .pipe(
      gulpif(
        config.enable.notify,
        notify({
          title: config.notify.title,
          message: 'Beautify js task complete',
          onLast: true,
        })
      )
    );
});

gulp.task('beautify:styles', () => {
  return gulp
    .src([`${config.styles.source}/**/*.scss`, `!${config.styles.source}/bootstrap/**/*.scss`, `!${config.styles.source}/mixins/**/*.scss`], {
      base: './',
      since: gulp.lastRun('beautify:styles'),
    })
    .pipe(changed(`${config.styles.source}`))
    .pipe(
      plumber({errorHandler: notify.onError('Error: <%= error.message %>')})
    )
    .pipe(postcss([stylefmt()], {syntax: syntaxScss}))
    .pipe(size({showFiles: true}))
    .pipe(plumber.stop())
    .pipe(gulp.dest('./'))
    .pipe(
      gulpif(
        config.enable.notify,
        notify({
          title: config.notify.title,
          message: 'Beautify css task complete',
          onLast: true,
        })
      )
    );
});

gulp.task('beautify', gulp.series('beautify:scripts', 'beautify:styles'));
