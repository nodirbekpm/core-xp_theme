<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function core_get_album_folders_tree_with_albums() {
    $top = get_terms( [
        'taxonomy' => 'album_folder',
        'hide_empty' => false,
        'parent' => 0,
        'orderby' => 'name',
        'order' => 'ASC',
    ] );
    if ( empty( $top ) || is_wp_error( $top ) ) return [];

    // helper to fetch children recursively and attach albums
    $build = function( $terms ) use ( &$build ) {
        $out = [];
        foreach ( $terms as $t ) {
            $term = clone $t;
            // children
            $children = get_terms( [
                'taxonomy' => 'album_folder',
                'hide_empty' => false,
                'parent' => $term->term_id,
                'orderby' => 'name',
            ] );
            $term->children = $children ? $build( $children ) : [];

            // direct albums assigned to this term
            $args = [
                'post_type' => 'album',
                'posts_per_page' => -1,
                'post_status' => 'publish',
                'tax_query' => [
                    [
                        'taxonomy' => 'album_folder',
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                        'include_children' => false, // direct only
                    ],
                ],
                'orderby' => 'date',
                'order' => 'DESC',
            ];
            $q = new WP_Query( $args );
            $term->albums = $q->posts ?: [];

            // flags default
            $term->is_open = false;
            $term->is_active = false;

            $out[] = $term;
        }
        return $out;
    };

    return $build( $top );
}

/**
 * Mark open/active flags on tree for a given album ID (open parent chain),
 * or for a given folder ID (open that branch).
 *
 * This mutates $tree (passed by reference).
 *
 * @param array &$tree
 * @param int|null $album_id
 * @param int|null $folder_id
 */
function core_mark_album_tree_active_flags( &$tree, $album_id = null, $folder_id = null ) {
    // if album selected, find its assigned term(s) and their ancestors
    $open_ids = [];
    if ( $album_id ) {
        $terms = get_the_terms( $album_id, 'album_folder' );
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            // take first term (if multiple)
            $t = array_shift( $terms );
            // get ancestors (returns array of IDs)
            $anc = get_ancestors( $t->term_id, 'album_folder', 'taxonomy' );
            // order from top -> child
            $anc = array_reverse( $anc );
            // include the term itself
            $anc[] = $t->term_id;
            $open_ids = $anc;
        }
    } elseif ( $folder_id ) {
        $anc = get_ancestors( $folder_id, 'album_folder', 'taxonomy' );
        $anc = array_reverse( $anc );
        $anc[] = $folder_id;
        $open_ids = $anc;
    }

    // recursive walker to set flags
    $walker = function( &$nodes ) use ( &$walker, $open_ids, $album_id ) {
        foreach ( $nodes as &$n ) {
            if ( in_array( $n->term_id, $open_ids, true ) ) {
                $n->is_open = true;
                $n->is_active = true;
            } else {
                $n->is_open = false;
                $n->is_active = false;
            }

            // also if album_id is inside this term's albums, mark the album's tab-btn to have active
            if ( $album_id && ! empty( $n->albums ) ) {
                foreach ( $n->albums as $ap ) {
                    if ( $ap->ID == $album_id ) {
                        // mark term active as well (so its inner-menu opens)
                        $n->is_open = true;
                        $n->is_active = true;
                        // and we can set a property on the album object
                        $ap->is_active = true;
                    } else {
                        $ap->is_active = false;
                    }
                }
            }

            if ( ! empty( $n->children ) ) {
                $walker( $n->children );
            }
        }
    };

    $walker( $tree );
}


/**
 * Get album posts assigned to $term_id.
 * By default returns only directly assigned posts.
 * If nothing found, optionally fallback to include children (useful if content assigned to child terms).
 *
 * @param int  $term_id
 * @param bool $include_children - default false (direct only)
 * @param bool $fallback_include_children - if true, do direct->if none->include_children
 * @return WP_Post[]
 */
function core_get_albums_by_folder( $term_id, $include_children = false, $fallback_include_children = true ) {
    $args = [
        'post_type'      => 'album',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'tax_query'      => [
            [
                'taxonomy'         => 'album_folder',
                'field'            => 'term_id',
                'terms'            => $term_id,
                'include_children' => $include_children,
            ],
        ],
        'orderby' => 'date',
        'order'   => 'DESC',
    ];

    $q = new WP_Query( $args );
    $posts = $q->posts;

    if ( empty( $posts ) && $fallback_include_children && ! $include_children ) {
        // Try again including children
        $args['tax_query'][0]['include_children'] = true;
        $q2 = new WP_Query( $args );
        $posts = $q2->posts;
    }

    return $posts;
}

/**
 * Get single album HTML (render template part)
 */
function core_render_single_album_html( $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post ) return '';

    ob_start();
    // make sure template sees $post
    $GLOBALS['post'] = $post;
    setup_postdata( $post );

    // template-parts/single-album.php should exist
    $template = locate_template( 'template-parts/single-album.php' );
    if ( $template ) {
        include $template;
    } else {
        // fallback minimal
        ?>
        <div class="news-single">
            <div class="news-top">
                <p class="date"><?php echo esc_html( get_the_date( 'H:i, j F Y', $post ) ); ?></p>
            </div>
            <h2><?php echo esc_html( get_the_title( $post ) ); ?></h2>
            <div class="content"><?php echo apply_filters( 'the_content', $post->post_content ); ?></div>
        </div>
        <?php
    }

    wp_reset_postdata();
    return ob_get_clean();
}

/**
 * AJAX: get albums in folder (renders list items or fallback)
 */
add_action( 'wp_ajax_nopriv_core_get_albums_by_folder_ajax', 'core_get_albums_by_folder_ajax' );
add_action( 'wp_ajax_core_get_albums_by_folder_ajax', 'core_get_albums_by_folder_ajax' );
function core_get_albums_by_folder_ajax() {
    $term_id = isset( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : 0;
    if ( ! $term_id ) {
        wp_send_json_error( [ 'message' => 'Invalid term' ] );
    }

    // First try direct only, fallback will include children
    $albums = core_get_albums_by_folder( $term_id, false, true );

    $titles = array_map( function( $p ){ return $p->post_title . '|' . $p->ID; }, $albums );

    ob_start();
    if ( ! empty( $albums ) ) {
        foreach ( $albums as $post ) {
            $GLOBALS['post'] = $post;
            setup_postdata( $post );
            // Try to include template part; if missing, fallback will produce minimal markup below
            $template = locate_template( 'template-parts/album-list-item.php' );
            if ( $template ) {
                include $template;
            } else {
                // fallback item markup
                ?>
                <div class="tab-btn album-item" data-album-id="<?php echo esc_attr( $post->ID ); ?>">
                    <a href="#" class="tab-head"><span><?php echo esc_html( get_the_title( $post ) ); ?></span></a>
                </div>
                <?php
            }
        }
        wp_reset_postdata();
    }
    $html = ob_get_clean();

    if ( empty( trim( $html ) ) ) {
        $html = '';
    }

    wp_send_json_success( [
        'html' => $html,
        'count' => count( $albums ),
        'titles' => $titles,
    ] );
}


/**
 * AJAX: get single album by id
 */
add_action( 'wp_ajax_nopriv_core_get_single_album_ajax', 'core_get_single_album_ajax' );
add_action( 'wp_ajax_core_get_single_album_ajax', 'core_get_single_album_ajax' );
function core_get_single_album_ajax() {
    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    if ( ! $post_id ) wp_send_json_error( [ 'message' => 'Invalid album' ] );

    $html = core_render_single_album_html( $post_id );
    wp_send_json_success( [ 'html' => $html, 'post_id' => $post_id ] );
}
