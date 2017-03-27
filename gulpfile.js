var gulp = require('gulp'),
    sass = require('gulp-sass'),
    minify = require('gulp-minify-css'),
    concat = require('gulp-concat'),
    uglify = require('gulp-uglify'),
    merge = require('merge-stream');

// move icons to public folder
gulp.task('icons', function () {
    return gulp.src('bower_components/font-awesome/fonts/**.*')
        .pipe(gulp.dest('web/fonts'));
});

gulp.task('css', function () {
    var scssStream = gulp.src([
        'bower_components/font-awesome/scss/font-awesome.scss',
        'app/Resources/assets/styles/styles.scss'
    ])
        .pipe(sass({
            style: 'compressed'
        }))
        .pipe(concat('scss-files.scss'));

    var cssStream = gulp.src('app/Resources/assets/styles/*.css')
        .pipe(concat('css-files.css'));

    return merge(scssStream, cssStream)
        .pipe(concat('styles.css'))
        .pipe(minify())
        .pipe(gulp.dest('web/css'));
});

gulp.task('js', function () {
    return gulp.src([
        'bower_components/jquery/dist/jquery.min.js'
    ]).pipe(concat('js.js'))
        .pipe(uglify())
        .pipe(gulp.dest('web/js'));
});

gulp.task('default', ['icons', 'css', 'js']);