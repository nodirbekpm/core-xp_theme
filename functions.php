<?php
/**
 * Theme Name: core
 * Description: Custom theme skeleton for news, events, documents, albums, employees
 * Text Domain: core
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'CORE_THEME_VERSION', wp_get_theme()->get( 'Version' ) ?: '1.0.0' );

/**
 * Theme setup
 */
function core_setup() {
    // i18n
    load_theme_textdomain( 'core', get_template_directory() . '/languages' );

    // Head & media
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ] );
    add_theme_support( 'custom-logo', [ 'height' => 80, 'width' => 240, 'flex-height' => true, 'flex-width' => true ] );

    // Menus
    register_nav_menus( [
        'primary' => __( 'Primary Menu', 'core' ),
        'footer'  => __( 'Footer Menu', 'core' ),
    ] );

    // Default image sizes (ixtiyoriy)
    // add_image_size( 'card', 720, 405, true );
}
add_action( 'after_setup_theme', 'core_setup' );

/**
 * Content width (ixtiyoriy)
 */
function core_content_width() {
    $GLOBALS['content_width'] = $GLOBALS['content_width'] ?? 800;
}
add_action( 'after_setup_theme', 'core_content_width', 0 );

/**
 * Assets (keyin real fayllarni qo'shamiz)
 */
function core_enqueue_assets() {
    $theme_uri = get_template_directory_uri();
    wp_enqueue_style( 'core-style', $theme_uri . '/assets/css/theme.css', [], CORE_THEME_VERSION );
    wp_enqueue_script( 'core-scripts', $theme_uri . '/assets/js/theme.js', [ 'jquery' ], CORE_THEME_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'core_enqueue_assets' );

/**
 * Include: Custom Post Types & Taxonomies
 */
require_once get_template_directory() . '/inc/custom-post-types.php';

/**
 * Flush rewrites on theme switch (CPT slugs uchun kerak)
 */
function core_flush_rewrite_on_switch() {
    // CPT & tax register bo‘lishi uchun vaqtincha include qilamiz
    require_once get_template_directory() . '/inc/custom-post-types.php';
    core_register_cpts_and_taxes();
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'core_flush_rewrite_on_switch' );


// Hide WP admin bar on the front-end for all logged-in users
add_filter( 'show_admin_bar', '__return_false' );



/**
 * Include: Sidebar user image
 */
require_once get_template_directory() . '/inc/sidebar-user-image.php';


/**
 * Breadcrumbs
 */
require_once get_template_directory() . '/inc/breadcrumbs.php';


/**
 * Include: Archive News
 */
require_once get_template_directory() . '/inc/render-news.php';


/**
 * Include: Archive Event
 */
require_once get_template_directory() . '/inc/render-event.php';

/**
 *  Likes & Comments
 */
require_once get_template_directory() . '/inc/likes-comments.php';

/**
 * Enqueue interaction script and localize REST base + nonce + login flag
 */
function core_enqueue_interaction_script() {
    $handle = 'core-interaction';
    $src = get_template_directory_uri() . '/assets/js/interaction.js';
    $ver = file_exists( get_template_directory() . '/assets/js/interaction.js' ) ? filemtime( get_template_directory() . '/assets/js/interaction.js' ) : false;

    wp_enqueue_script( $handle, $src, [], $ver, true );

    wp_localize_script( $handle, 'coreRest', [
        'root'            => esc_url_raw( rest_url() ),
        'nonce'           => wp_create_nonce( 'wp_rest' ),
        'is_user_logged_in' => is_user_logged_in() ? 1 : 0,
        'login_url'       => wp_login_url( get_permalink() ), // redirect back after login
    ] );
}
add_action( 'wp_enqueue_scripts', 'core_enqueue_interaction_script' );


/**
 *  Share js
 */
function core_enqueue_share_script() {
    wp_enqueue_script(
        'core-share',
        get_template_directory_uri() . '/assets/js/share.js',
        [],
        filemtime( get_template_directory() . '/assets/js/share.js' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'core_enqueue_share_script' );


/**
 *  Documents logikasi
 */
require_once get_template_directory() . '/inc/documents-functions.php';

function core_enqueue_documents_scripts() {
    wp_enqueue_script( 'core-documents', get_template_directory_uri() . '/assets/js/documents.js', [], '1.0', true );

    // lokalizatsiya: front-endda admin-ajax url berish
    wp_localize_script( 'core-documents', 'coreDocs', [
        'ajaxurl' => admin_url( 'admin-ajax.php' )
    ] );
}
add_action( 'wp_enqueue_scripts', 'core_enqueue_documents_scripts' );


/**
 *  Album logikasi
 */

require_once get_template_directory() . '/inc/albums-functions.php';

function theme_enqueue_albums_scripts() {
    wp_enqueue_script( 'core-albums', get_template_directory_uri() . '/assets/js/albums.js', array(), '1.0.0', true );
    wp_localize_script( 'core-albums', 'coreAlbums', array(
        'ajaxurl'    => admin_url( 'admin-ajax.php' ),
        'selectText' => __( 'Выберите альбом', 'core' ),
    ) );
}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_albums_scripts' );



/**
 *  Employee logikasi
 */

require_once get_template_directory() . '/inc/employees-functions.php';

function core_enqueue_employees_scripts() {
    wp_enqueue_script( 'core-employees', get_template_directory_uri() . '/assets/js/employees.js', [], '1.0', true );
    wp_localize_script( 'core-employees', 'coreEmployees', [
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'employees_filter' ),
    ] );
}
add_action( 'wp_enqueue_scripts', 'core_enqueue_employees_scripts' );


