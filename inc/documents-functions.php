<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get hierarchical doc_folder terms (top-level with children)
 * @return array WP_Term objects tree
 */
function core_get_doc_folders_tree() {
    $terms = get_terms( array(
        'taxonomy' => 'doc_folder',
        'hide_empty' => false,
        'parent' => 0,
        'orderby' => 'name',
        'order' => 'ASC',
    ) );

    if ( empty( $terms ) || is_wp_error( $terms ) ) {
        return [];
    }

    // attach children
    foreach ( $terms as $term ) {
        $children = get_terms( array(
            'taxonomy' => 'doc_folder',
            'hide_empty' => false,
            'parent' => $term->term_id,
            'orderby' => 'name',
        ) );
        $term->children = $children ?: [];
    }

    return $terms;
}

/**
 * Get documents for a given folder term_id.
 * By default returns only posts directly assigned to the term (no children).
 *
 * @param int  $term_id
 * @param bool $include_children If true, include children terms as well.
 * @return WP_Post[]
 */
function core_get_documents_by_folder( $term_id, $include_children = false ) {
    $args = array(
        'post_type'      => 'document',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy'         => 'doc_folder',
                'field'            => 'term_id',
                'terms'            => $term_id,
                'include_children' => $include_children,
            ),
        ),
        'orderby' => 'date',
        'order'   => 'DESC',
    );
    $q = new WP_Query( $args );
    return $q->posts;
}



/**
 * Render download link (file field) for a document post id
 * returns array with 'url' and 'label' or false
 */
function core_get_document_file_data( $post_id ) {
    $file = get_field( 'file', $post_id ); // assuming ACF 'file' field
    if ( empty( $file ) ) {
        return false;
    }
    // if return_format = array
    $url = is_array( $file ) && ! empty( $file['url'] ) ? $file['url'] : ( is_string( $file ) ? $file : '' );
    $filename = is_array( $file ) && ! empty( $file['filename'] ) ? $file['filename'] : basename( $url );
    return array(
        'url' => $url,
        'label' => $filename,
    );
}



// AJAX handler for front-end (non-privileged)
add_action( 'wp_ajax_nopriv_core_get_documents_by_folder_ajax', 'core_get_documents_by_folder_ajax' );
add_action( 'wp_ajax_core_get_documents_by_folder_ajax', 'core_get_documents_by_folder_ajax' );

function core_get_documents_by_folder_ajax() {
    if ( ! isset( $_POST['term_id'] ) ) {
        wp_send_json_error( [ 'message' => 'No term provided' ] );
    }

    $term_id = intval( $_POST['term_id'] );
    if ( ! $term_id ) {
        wp_send_json_error( [ 'message' => 'Invalid term', 'term_id' => $term_id ] );
    }

    // include_children option (default false)
    $include_children = isset( $_POST['include_children'] ) && filter_var( $_POST['include_children'], FILTER_VALIDATE_BOOLEAN );

    $term = get_term( $term_id, 'doc_folder' );
    $term_info = $term && ! is_wp_error( $term ) ? [
        'term_id' => $term->term_id,
        'name'    => $term->name,
        'slug'    => $term->slug,
        'count'   => $term->count
    ] : null;

    // Get docs (direct only by default)
    $docs = core_get_documents_by_folder( $term_id, $include_children );

    // titles for debug/fallback
    $titles = array_map( function( $p ){ return $p->post_title . '|' . $p->ID; }, $docs );

    // Render HTML — ensure global $post is properly set for template part
    ob_start();

    if ( ! empty( $docs ) ) {
        foreach ( $docs as $doc ) {
            // set global $post and setup_postdata for template compatibility
            $GLOBALS['post'] = $doc;
            setup_postdata( $doc );

            // Prefer include so that $post is visible inside template
            $template = locate_template( 'template-parts/document-item.php' );
            if ( $template ) {
                include $template;
            } else {
                // fallback minimal rendering if template missing
                $title = esc_html( get_the_title( $doc ) );
                $pub = get_field( 'published_at', $doc->ID ) ?: get_the_date( 'H:i, j F Y', $doc );
                $author = get_field( 'author_name', $doc->ID ) ?: get_the_author_meta( 'display_name', $doc->post_author );
                $file = core_get_document_file_data( $doc->ID );
                $download_url = $file ? $file['url'] : get_permalink( $doc->ID );
                $download_label = $file ? $file['label'] : __( 'Open', 'core' );
                $preview = get_field( 'preview_image', $doc->ID );
                $preview_url = $preview && is_array( $preview ) ? ( $preview['sizes']['medium'] ?? $preview['url'] ) : get_template_directory_uri() . '/assets/img/document-img.png';

                ?>
                <div class="document-item" data-post-id="<?php echo esc_attr( $doc->ID ); ?>">
                    <img src="<?php echo esc_url( $preview_url ); ?>" alt="" class="document-img">
                    <div class="document-right">
                        <div class="item-title"><?php echo $title; ?></div>
                        <div class="document-texts">
                            <span class="event-desc"><?php echo esc_html( $pub ); ?></span>
                            <span class="doc-user-name"><?php echo esc_html( $author ); ?></span>
                        </div>
                        <a href="<?php echo esc_url( $download_url ); ?>" class="btn-main btn-download" target="_blank" rel="noopener noreferrer">
                            <?php echo esc_html( $download_label ); ?>
                        </a>
                    </div>
                </div>
                <?php
            }
        }
        wp_reset_postdata();
    }

    $html = ob_get_clean();

    // If template produced empty HTML (rare), build fallback
    if ( empty( trim( $html ) ) ) {
        if ( ! empty( $docs ) ) {
            $fallback = '<div class="documents-row">';
            foreach ( $docs as $p ) {
                $title = esc_html( get_the_title( $p ) );
                $pub = get_field( 'published_at', $p->ID ) ?: get_the_date( 'H:i, j F Y', $p );
                $author = get_field( 'author_name', $p->ID ) ?: get_the_author_meta( 'display_name', $p->post_author );
                $file = core_get_document_file_data( $p->ID );
                $download_url = $file ? $file['url'] : get_permalink( $p->ID );
                $download_label = $file ? $file['label'] : __( 'Open', 'core' );
                $preview = get_field( 'preview_image', $p->ID );
                $preview_url = $preview && is_array($preview) ? ( $preview['sizes']['medium'] ?? $preview['url'] ) : get_template_directory_uri() . '/assets/img/document-img.png';

                $fallback .= '<div class="document-item" data-post-id="'. esc_attr( $p->ID ) .'">';
                $fallback .= '<img src="'. esc_url( $preview_url ) .'" alt="" class="document-img">';
                $fallback .= '<div class="document-right">';
                $fallback .= '<div class="item-title">'. $title .'</div>';
                $fallback .= '<div class="document-texts"><span class="event-desc">'. esc_html( $pub ) .'</span><span class="doc-user-name">'. esc_html( $author ) .'</span></div>';
                $fallback .= '<a href="'. esc_url( $download_url ) .'" class="btn-main btn-download" target="_blank" rel="noopener noreferrer">'. esc_html( $download_label ) .'</a>';
                $fallback .= '</div></div>';
            }
            $fallback .= '</div>';
            $html = $fallback;
        } else {
            $html = '<p>' . esc_html__( 'Документы не найдены', 'core' ) . '</p>';
        }
    }

    wp_send_json_success( [
        'term' => $term_info,
        'docs_count' => count( $docs ),
        'titles' => $titles,
        'html' => $html
    ] );
}




