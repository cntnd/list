var gulp        = require('gulp');
var sass        = require('gulp-sass');
var minify      = require('gulp-minifier');

gulp.task('watch', function () {
    gulp.watch("src/scss/**/*.scss", gulp.series('sass'));
});

// Compile sass into CSS
gulp.task('sass', function() {
    return gulp.src("src/scss/*.scss")
        .pipe(sass())
        .pipe(minify({
          minify: true,
          minifyCSS: true,
          getKeptComment: function (content, filePath) {
              var m = content.match(/\/\*![\s\S]*?\*\//img);
              return m && m.join('\n') + '\n' || '';
          }
        }))
        .pipe(gulp.dest("src/css/"));
});

gulp.task('default', gulp.series('sass','watch'));
