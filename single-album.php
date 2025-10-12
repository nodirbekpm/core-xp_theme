<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;
if ( ! $post || ! isset( $post->ID ) ) {
    // fallback to normal single
    get_header();
    the_content();
    get_footer();
    exit;
}

// Build archive link
$archive = get_post_type_archive_link( 'album' );
if ( $archive ) {
    $url = add_query_arg( 'album_id', $post->ID, $archive );
    // safe redirect (302). Use 301 if desired but 302 is safer during dev.
    wp_safe_redirect( esc_url_raw( $url ) );
    exit;
}

// if can't find archive, render normal single fallback
get_header();
setup_postdata( $post );
?>
    <div class="content">
        <?php echo core_render_single_album_html( $post->ID ); ?>
    </div>
<?php
wp_reset_postdata();
get_footer();
