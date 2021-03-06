// GULP PACKAGES
// Most packages are lazy loaded
var gulp  = require('gulp'),
    gutil = require('gulp-util'),
    browserSync = require('browser-sync').create(),
    filter = require('gulp-filter'),
    rename = require('gulp-rename'),
    plugin = require('gulp-load-plugins')();


// GULP VARIABLES
// Modify these variables to match your project needs

// Set local URL if using Browser-Sync
const LOCAL_URL = 'http://jointswp-github.dev/';

// Set path to Foundation files
const MODULES = 'node_modules';
const BOOTSTRAP = MODULES + '/bootstrap';
const VENDORS = 'assets/vendors';

// Select Foundation components, remove components project will not use
const SOURCE = {
    scripts: [

        MODULES + '/popper.js/dist/umd/popper.js',

        // Foundation core - needed if you want to use any of the components below
        BOOTSTRAP + '/dist/js/bootstrap.js',
        // BOOTSTRAP + '/js/dist/index.js,',
        // BOOTSTRAP + '/js/dist/alert.js',
        // BOOTSTRAP + '/js/dist/button.js',
        // BOOTSTRAP + '/js/dist/carousel.js',
        // BOOTSTRAP + '/js/dist/collapse.js',
        // BOOTSTRAP + '/js/dist/dropdown.js',
        // BOOTSTRAP + '/js/dist/modal.js',
        // BOOTSTRAP + '/js/dist/popover.js',
        // BOOTSTRAP + '/js/dist/scrollspy.js',
        // BOOTSTRAP + '/js/dist/tab.js',
        // BOOTSTRAP + '/js/dist/toast.js',
        // BOOTSTRAP + '/js/dist/tooltip.js',
        // BOOTSTRAP + '/js/dist/util.js',
        // BOOTSTRAP + '/js/dist/*.js',

        VENDORS + '/jquery.easing.js',
        VENDORS + '/bootstrap-datepicker.js',
        VENDORS + '/plugins.js',
        VENDORS + '/fitvids.js',
        VENDORS + '/superfish.menu.js',
        VENDORS + '/hoverIntent.js',
        VENDORS + '/jRespond.js',
        VENDORS + '/jquery.mb.YTPlayer.js',
        VENDORS + '/js-cookie.js',
        VENDORS + '/jquery.appear.js',
        VENDORS + '/animsition.js',
        VENDORS + '/skrollr.js',
        VENDORS + '/owlCarousel.js',
        VENDORS + '/Morphext.js',
        VENDORS + '/isotope.js',
        VENDORS + '/imagesLoaded.js',
        VENDORS + '/Swiper.js',
        VENDORS + '/jquery-hashchange-plugin.js',
        VENDORS + '/theia-sticky-sidebar.js',
        VENDORS + '/jquery-color.js',
        VENDORS + '/jquery-form.js',
        VENDORS + '/magnific-popup.js',
        // VENDORS + '/FlexSlider.js',
        VENDORS + '/jQuery-Paginate.js',
        VENDORS + '/infinite-scroll.js',
        VENDORS + '/jqueryui.js',
        VENDORS + '/jqueryvalidation.js',
        VENDORS + '/jquery.matchHeight.js',

        // Place custom JS here, files will be concantonated, minified if ran with --production
        'assets/scripts/js/**/*.js',
    ],

    fonts: [
        MODULES + '/@fortawesome/fontawesome-free/webfonts/**/*.{ttf,woff,woff2,eof,svg}'
    ],

    // Scss files will be concantonated, minified if ran with --production
    styles: 'assets/styles/scss/**/*.scss',

    // Images placed here will be optimized
    images: 'assets/images/**/*',

    php: '**/*.php'
};

const ASSETS = {
    styles: 'assets/styles/',
    scripts: 'assets/scripts/',
    images: 'assets/images/',
    fonts: 'assets/fonts/',
    vendors: 'assets/vendors/',
    all: 'assets/'
};

const JSHINT_CONFIG = {
    "node": true,
    "globals": {
        "document": true,
        "window": true,
        "jQuery": true
    }
};

// GULP FUNCTIONS
// JSHint, concat, and minify JavaScript
gulp.task('scripts', function() {

    // Use a custom filter so we only lint custom JS
    const CUSTOMFILTER = filter(ASSETS.scripts + 'js/**/*.js', {restore: true});

    return gulp.src(SOURCE.scripts)
        .pipe(plugin.plumber(function(error) {
            gutil.log(gutil.colors.red(error.message));
            this.emit('end');
        }))
        .pipe(plugin.sourcemaps.init())
        .pipe(CUSTOMFILTER)
        .pipe(plugin.jshint(JSHINT_CONFIG))
        .pipe(plugin.jshint.reporter('jshint-stylish'))
        .pipe(CUSTOMFILTER.restore)
        .pipe(plugin.concat('scripts.js'))
        .pipe(gulp.dest(ASSETS.scripts))
        .pipe(rename({suffix: '.min'}))
        .pipe(plugin.uglify())
        .pipe(plugin.sourcemaps.write('.')) // Creates sourcemap for minified JS
        .pipe(gulp.dest(ASSETS.scripts))
});

gulp.task('fonts', function() {
    return gulp.src(SOURCE.fonts)
        .pipe(gulp.dest(ASSETS.fonts));
});

// Compile Sass, Autoprefix and minify
gulp.task('styles', function() {
    return gulp.src(SOURCE.styles)
        .pipe(plugin.plumber(function(error) {
            gutil.log(gutil.colors.red(error.message));
            this.emit('end');
        }))
        .pipe(plugin.sourcemaps.init())
        .pipe(plugin.sass())
        .pipe(plugin.autoprefixer({
            browsers: [
                'last 2 versions',
                'ie >= 9',
                'ios >= 7'
            ],
            cascade: false
        }))
        .pipe(gulp.dest(ASSETS.styles))
        .pipe(rename({suffix: '.min'}))
        .pipe(plugin.cssnano())
        .pipe(plugin.sourcemaps.write('.'))
        .pipe(gulp.dest(ASSETS.styles))
        .pipe(browserSync.reload({
            stream: true
        }));
});

// Optimize images, move into assets directory
gulp.task('images', function() {
    return gulp.src(SOURCE.images)
        .pipe(plugin.imagemin())
        .pipe(gulp.dest(ASSETS.images))
});

gulp.task( 'translate', function () {
    return gulp.src( SOURCE.php )
        .pipe(plugin.wpPot( {
            domain: 'jointswp',
            package: 'Example project'
        } ))
        .pipe(gulp.dest('file.pot'));
});

// Browser-Sync watch files and inject changes
gulp.task('browsersync', function() {

    // Watch these files
    var files = [
        SOURCE.php,
    ];

    browserSync.init(files, {
        proxy: LOCAL_URL,
    });

    gulp.watch(SOURCE.styles, gulp.parallel('styles'));
    gulp.watch(SOURCE.scripts, gulp.parallel('scripts')).on('change', browserSync.reload);
    gulp.watch(SOURCE.images, gulp.parallel('images'));

});

// Watch files for changes (without Browser-Sync)
gulp.task('watch', function() {

    // Watch .scss files
    gulp.watch(SOURCE.styles, gulp.parallel('styles'));

    // Watch scripts files
    gulp.watch(SOURCE.scripts, gulp.parallel('scripts'));

    // Watch images files
    // gulp.watch(SOURCE.images, gulp.parallel('images'));

});

// Run styles, scripts and foundation-js
gulp.task('default', gulp.parallel('styles', 'scripts', 'fonts', 'images'));
