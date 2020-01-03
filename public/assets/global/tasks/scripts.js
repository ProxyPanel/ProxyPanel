import gulp from 'gulp';
import config from '../config';
import eslint from 'gulp-eslint';
import gulpif from 'gulp-if';
import size from 'gulp-size';
import plumber from 'gulp-plumber';
import uglifyjs from 'uglify-js';
import composer from 'gulp-uglify/composer';
import gutil from 'gulp-util';
import notify from 'gulp-notify';
import notifier from 'node-notifier';
import babel from 'gulp-babel';
import handleErrors from './utils/handleErrors';
import rollup from 'gulp-rollup';
import header from 'gulp-header';
import Glob from 'glob-fs';
import path from 'path';
import del from 'del';

// SCRIPTS
// ------------------
gulp.task('lint:scripts', () => {
  return gulp
    .src(`${config.scripts.source}/**/*.js`, {
      base: './',
      since: gulp.lastRun('lint:scripts'),
    })
    .pipe(eslint({fix: true})) // see http://eslint.org/docs/rules/
    .pipe(eslint.format())
    .pipe(gulp.dest('.'));
});

gulp.task('make:scripts', (done) => {
  const uglify = composer(uglifyjs, console);
  const glob = Glob();
  const files = glob.readdirSync(path.join(config.scripts.source, '**/*.js'));

  const globals = {
    jquery: 'jQuery',
    Component: 'Component',
    Plugin: 'Plugin',
    Config: 'Config',
    GridMenu: "SectionGridMenu",
    Menubar: "SectionMenubar",
    PageAside: "SectionPageAside",
    Sidebar: "SectionSidebar"
  };

  const external = Object.keys(globals);

  return gulp
    .src(`${config.scripts.source}/**/*.js`)
    .on('error', handleErrors)
    .pipe(
      plumber({
        errorHandler: notify.onError('Error: <%= error.message %>'),
      })
    )
    .pipe(rollup({
      input: files,
      // rollup: require('rollup'),
      allowRealFiles: true,
      output: {
        globals: globals,
        format: 'es'
      },
      external: external,
    }))
    .pipe(babel({
      babelrc: false,
      presets: [
        [
          '@babel/preset-env'
        ]
      ],
      moduleRoot: '',
      moduleIds: true,
      plugins: [
        ["@babel/plugin-transform-modules-umd", {
          "globals": globals
        }],
        "@babel/plugin-proposal-object-rest-spread",
        "@babel/plugin-proposal-class-properties",
        "@babel/plugin-external-helpers"
      ]
    }))
    .pipe(gulpif(config.production, uglify()))
    .pipe(gulpif(config.production, header(config.banner)))
    .pipe(size({gzip: true, showFiles: true}))
    .pipe(gulp.dest(`${config.scripts.build}`));
});

gulp.task(
  'scripts',
  gulp.series('lint:scripts', 'make:scripts', (done) => {
    if (config.enable.notify) {
      notifier.notify({
        title: config.notify.title,
        message: 'Scripts task complete',
      });
    }

    done();
  })
);

// Clean scripts files
gulp.task('clean:scripts', (done) => {
  return del([`${config.scripts.build}/**/*`]).then(() => {
    if (config.enable.notify) {
      notifier.notify({
        title: config.notify.title,
        message: 'Clean scripts task complete',
      });
    }

    done();
  });
});
