const gulp = require('gulp');
const uglify = require("gulp-uglify");
const cleanCss = require("gulp-clean-css");
const eslint = require("gulp-eslint");
const rename = require("gulp-rename");
const sass = require('gulp-sass');

const file = 'jquery.nestable';

// compress js
gulp.task('js', function () {
    gulp.src(file + '.js')
        .pipe(uglify())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('dist/'));
});

// compile SASS to CSS
gulp.task('sass', function () {
    return gulp.src(file + '.scss')
        .pipe(sass().on('error', sass.logError))
        .pipe(gulp.dest('.'));
});

// compress css
gulp.task('css', ['sass'], function () {
    gulp.src(file + '.css')
        .pipe(cleanCss())
        .pipe(rename({suffix: '.min'}))
        .pipe(gulp.dest('dist/'));
});

gulp.task('test', function () {
    return gulp.src([file + '.js'])
        .pipe(eslint())
        .pipe(eslint.format())
        .pipe(eslint.failAfterError());
});

// build assets
gulp.task('default', ['js', 'css']);