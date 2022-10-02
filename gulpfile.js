var gulp        = require('gulp');
var sass        = require('gulp-sass')(require('sass'));
var minify      = require('gulp-minifier');
var zip         = require('gulp-zip');
var file        = require('gulp-file');
var del         = require('del');
var pkg         = require('./package.json');

gulp.task('watch', function () {
    gulp.watch('src/scss/**/*.scss', gulp.series('sass'));
});

// Compile sass into CSS
gulp.task('sass', function() {
    return gulp.src('src/scss/**/*.scss')
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

gulp.task('xampp', function () {
    return gulp.src(['src/**','!src/{scss,scss/**}','!src/{sql,sql/**}'])
        .pipe(gulp.dest('modules/'+pkg.name));
});

gulp.task('zip', function() {
    return gulp.src(['src/**','!src/{scss,scss/**}','!src/{sql,sql/**}'])
  		.pipe(zip(pkg.name+'.zip'))
  		.pipe(gulp.dest('dist'));
});

gulp.task('clean', function () {
    return del(['dist/**/*','modules/**/*']);
});

// creates info.xml
gulp.task('info-xml', function () {
    var infoXml =
        '<?xml version="1.0" encoding="UTF-8"?>\n' +
        '<module>\n' +
        '<name>'+pkg.name+'</name>\n' +
        '<description>\n' +
        pkg.name+'\n' +
        pkg.description+'\n' +
        '\n' +
        'Autor '+pkg.author+'\n' +
        '\n' +
        'Version '+pkg.version+'\n' +
        '</description>\n' +
        '<type/>\n' +
        '<alias>'+pkg.name+'</alias>\n' +
        '</module>';

    return file('info.xml', infoXml, {src: true})
        .pipe(gulp.dest('src/'));
});

gulp.task('default', gulp.series('sass','watch'));

gulp.task('dist', gulp.series('clean','sass','info-xml','zip'));

gulp.task('module', gulp.series('clean','sass','info-xml','xampp'));

gulp.task('init', gulp.series('clean','sass','info-xml'));
