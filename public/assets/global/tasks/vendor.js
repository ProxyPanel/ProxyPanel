import config from '../config';
import gulp from 'gulp';
import notifier from 'node-notifier';
import AssetsManager from 'assets-manager';
import sass from 'gulp-sass';
import postcss from 'gulp-postcss';
import size from 'gulp-size';
import minify from 'gulp-clean-css';
import rename from 'gulp-rename';
import gulpif from 'gulp-if';
import notify from 'gulp-notify';
import del from 'del';

gulp.task('clean:vendor', (done) => {
  const manager = new AssetsManager('manifest.json', config.vendor);

  manager.cleanPackages().then(()=>{
    if (config.enable.notify) {
      notifier.notify({
        title: config.notify.title,
        message: 'Vendor clean task complete',
      });
    }
    done();
  });
});

gulp.task('copy:vendor', (done) => {
  // see https://github.com/amazingSurge/assets-manager
  const manager = new AssetsManager('manifest.json', config.vendor);

  manager.copyPackages().then(()=>{
    done();
  });
});

gulp.task('vendor:styles', (done) => {
  return gulp
    .src(`${config.vendor.source}/*/*.scss`)
    .pipe(
      sass({
        precision: 10, // https://github.com/sass/sass/issues/1122
        includePaths: config.styles.include,
      })
    )
    .pipe(postcss())
    .pipe(size({gzip: true, showFiles: true}))
    .pipe(gulp.dest(`${config.vendor.dest}`))
    .pipe(minify())
    .pipe(rename({
      extname: '.min.css'
    }))
    .pipe(size({gzip: true, showFiles: true}))
    .pipe(gulp.dest(`${config.vendor.dest}`))
    .pipe(
      gulpif(
        config.enable.notify,
        notify({
          title: config.notify.title,
          message: 'Vendor task complete',
          onLast: true,
        })
      )
    );
});

gulp.task(
  'vendor',
  gulp.series('copy:vendor', 'vendor:styles', (done) => {
    if (config.enable.notify) {
      notifier.notify({
        title: config.notify.title,
        message: 'Vendor task complete',
      });
    }

    done();
  })
);
