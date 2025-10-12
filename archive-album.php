<?php
if ( ! is_user_logged_in() ) {
    nocache_headers();
    wp_safe_redirect( home_url('/') );
    exit;
}

get_header();

// build tree with albums included
$folders = core_get_album_folders_tree_with_albums();

$selected_folder_id = isset($_GET['folder_id']) ? intval($_GET['folder_id']) : 0;
$selected_album_id  = isset($_GET['album_id']) ? intval($_GET['album_id']) : 0;

// if pretty permalink (single album) redirected here with ?album_id by single template
// Mark flags on tree so parents open if album selected
core_mark_album_tree_active_flags( $folders, $selected_album_id ? $selected_album_id : null, $selected_folder_id ? $selected_folder_id : null );

// recursive render function
function render_album_folder_tree_with_flags( $items ) {
    if ( empty( $items ) ) return;
    foreach ( $items as $term ) {
        $has_children = ! empty( $term->children );
        $has_albums = ! empty( $term->albums );

        $open_class = ! empty( $term->is_open ) ? ' is-open' : '';
        $active_class = ! empty( $term->is_active ) ? ' active-folder' : '';

        echo '<div class="tab-btn'. $open_class . $active_class .'" data-folder-id="'. esc_attr( $term->term_id ) .'">';

        echo '<a href="#" class="tab-head" data-term-link="'. esc_attr( get_term_link( $term ) ) .'" data-href="'. esc_url( add_query_arg( 'folder_id', $term->term_id, get_post_type_archive_link('album') ) ) .'">';
        echo '<span class="tab-arr"><img src="'. get_template_directory_uri() .'/assets/img/icons/arr-right2.svg" alt=""></span>';
        echo '<span>'. esc_html( $term->name ) .'</span>';
        echo '</a>';

        echo '<div class="inner-menu">';
        // child folders
        if ( $has_children ) {
            render_album_folder_tree_with_flags( $term->children );
        }
        // direct albums
        if ( $has_albums ) {
            foreach ( $term->albums as $album_post ) {
                $alb_active = ! empty( $album_post->is_active ) ? ' active-folder' : '';
                echo '<div class="tab-btn album-item'. $alb_active .'" data-album-id="'. esc_attr( $album_post->ID ) .'">';
                echo '<a href="#" class="tab-head" data-album-link="'. esc_attr( get_permalink( $album_post->ID ) ) .'">';
                echo '<span>'. esc_html( get_the_title( $album_post ) ) .'</span>';
                echo '</a>';
                echo '</div>';
            }
        }

        echo '</div>'; // inner-menu
        echo '</div>'; // tab-btn
    }
}
?>

<div class="tab-menu-wrap">
    <div class="tab-menu" id="album-folders" data-selected="<?php echo esc_attr( $selected_folder_id ); ?>" data-selected-album="<?php echo esc_attr( $selected_album_id ); ?>">
        <?php render_album_folder_tree_with_flags( $folders ); ?>
    </div>
</div>

<div class="content">
    <div class="section-top">
        <div class="subheader">
            <img src="<?php echo get_template_directory_uri() ?>/assets/img/icons/albom-icon.png" alt="">
            Альбомы
        </div>
    </div>

    <div class="tab-wrap" id="album-content">
        <div class="tab-item is-active">
            <div id="album-single-container">
                <?php
                if ( $selected_album_id ) {
                    // optionally pre-render single album server-side to avoid extra AJAX call:
                    echo core_render_single_album_html( $selected_album_id );
                } else {
                    // placeholder
                    echo '<p>' . esc_html__( 'Выберите папку или альбом слева', 'core' ) . '</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<?php get_footer(); ?>
