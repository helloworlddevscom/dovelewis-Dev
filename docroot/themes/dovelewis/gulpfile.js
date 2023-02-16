/**
 * Gulp File.
 *
 * To compile SCSS: gulp build-css
 * To compile JS: gulp build-js
 * To compile all: gulp build
 * To compile and watch SCSS and JS: gulp watch
 * To run sass-lint on SCSS: gulp lint-scss
 */

const gulp = require('gulp');
const sass = require('gulp-sass');
const plumber = require('gulp-plumber');
const sourcemaps = require('gulp-sourcemaps');
const autoprefixer = require('gulp-autoprefixer');
const glob = require('gulp-sass-glob');
const minifycss = require('gulp-clean-css');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const sassLint = require('gulp-sass-lint');
const addsrc = require('gulp-add-src');
const notify = require('gulp-notify');

// Task: Compile CSS.
gulp.task('compile-css', function() {
  return gulp
    .src('./scss/style.scss')
    .pipe(addsrc('./scss/style-admin.scss'))
    .pipe(addsrc('./scss/style-admin-frontend.scss'))
    .pipe(
      plumber({
        errorHandler: function(error) {
          notify.onError({
            title: 'Gulp',
            subtitle: 'Failure!',
            message: 'Error: <%= error.message %>',
            sound: 'Beep'
          })(error);
          this.emit('end');
        },
      })
    )
    .pipe(sourcemaps.init())
    .pipe(glob())
    .pipe(
      sass({
        includePaths: ['./node_modules/foundation-sites/scss'],
        errLogToConsole: true,
      })
    )
    .pipe(
      autoprefixer({
        Browserslist: ['last 25 versions']
      })
    )
    .pipe(minifycss())
    .pipe(rename({ suffix: '.min' }))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest('./build/css'));
});

// Task: Lint SCSS files using sass-lint.
gulp.task('lint-scss', function() {
  return gulp
    .src(['./scss/**/*.scss'])
    .pipe(sassLint({
      options: {
        configFile: './.sass-lint.yml',
        formatter: 'stylish'
      },
    }))
    .pipe(sassLint.format())
    .pipe(sassLint.failOnError());
});

// Task: Build CSS and run sass-lint to notify of any errors (but not fix them).
gulp.task('build-css', gulp.series('compile-css', 'lint-scss'));

// Task: Build JS.
gulp.task('build-js', function() {
  // Global.js will be loaded on all pages in dovelewis.libraries.yml.
  // If we have JS that should not be loaded on all pages we should
  // minify it to a separate file and create a new Drupal library for it.
  // @TODO: Can we target everything in the /js directory without having to
  // explicitly write it out?
  return gulp
    .src([
      './js/global.js',
      './js/slick-carousel-init.js',
      './js/jquery-modal-init.js'
    ])
    .pipe(uglify())
    .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest('./build/js'));
});

// Task: Build both CSS and JS.
gulp.task('build', gulp.series('build-css', 'build-js'));

// Task: Watch both CSS and JS.
gulp.task('watch', function() {
  gulp.watch('./scss/**/*.scss', gulp.series('build-css'));
  gulp.watch('./js/**/*.js', gulp.series('build-js'));
});

// Task: Default Task.
gulp.task('default', gulp.series('build-css', 'build-js', 'watch'));
