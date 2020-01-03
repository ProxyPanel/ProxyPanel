import gulp from 'gulp';
import config from '../config';
import sass from 'gulp-sass';
import plumber from 'gulp-plumber';
import gulpif from 'gulp-if';
import size from 'gulp-size';
import stylelint from 'stylelint';
import postcss from 'gulp-postcss';
import syntaxScss from 'postcss-scss';
import reporter from 'postcss-reporter';
import notify from 'gulp-notify';
import notifier from 'node-notifier';
import header from 'gulp-header';
import minify from 'gulp-clean-css';
import rename from 'gulp-rename';
import del from 'del';

// STYLES
// ------------------
// lints styles using stylelint (config under 'stylelint' in package.json)
gulp.task('lint:styles', () => {
  return gulp
    .src([`${config.styles.source}/**/*.scss`, `!${config.styles.source}/bootstrap/**/*.scss`, `!${config.styles.source}/mixins/**/*.scss`], {
      base: './',
      since: gulp.lastRun('lint:styles'),
    })
    .pipe(
      postcss(
        [
          stylelint({
            fix: true,
            syntax: 'scss',
          }), // see http://stylelint.io/user-guide/example-config/
          reporter({clearMessages: true, clearReportedMessages: true}),
        ],
        {syntax: syntaxScss}
      )
    )
    .pipe(gulp.dest('./'));
});

// Compiles sass into css & minifies it (production)
gulp.task('make:styles', () => {
  return gulp
    .src(`${config.styles.source}/*.scss`)
    .pipe(
      plumber({errorHandler: notify.onError('Error: <%= error.message %>')})
    )
    .pipe(
      sass({
        precision: 10, // https://github.com/sass/sass/issues/1122
        includePaths: config.styles.include,
      })
    )
    .pipe(postcss())
    .pipe(gulpif(config.production, header(config.banner)))
    .pipe(size({gzip: true, showFiles: true}))
    .pipe(gulp.dest(`${config.styles.build}`))
    .pipe(minify())
    .pipe(rename({
      extname: '.min.css'
    }))
    .pipe(size({gzip: true, showFiles: true}))
    .pipe(plumber.stop())
    .pipe(gulp.dest(`${config.styles.build}`));
});

gulp.task(
  'styles',
  gulp.series('lint:styles', 'make:styles', (done) => {
    if (config.enable.notify) {
      notifier.notify({
        title: config.notify.title,
        message: 'Styles task complete',
      });
    }

    done();
  })
);

// Clean styles files
gulp.task('clean:styles', (done) => {
  return del([`${config.styles.build}/**/*`]).then(() => {
    if (config.enable.notify) {
      notifier.notify({
        title: config.notify.title,
        message: 'Clean styles task complete',
      });
    }

    done();
  });
});
