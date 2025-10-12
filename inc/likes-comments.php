<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Helper: render single comment HTML (return string)
 */
function core_render_comment_html( $comment ) {
    // $comment can be WP_Comment object or array
    if ( is_array( $comment ) ) {
        $comment = (object) $comment;
    }

    $author_name = esc_html( $comment->comment_author );
    $avatar = get_avatar( $comment->comment_author_email, 48 );
    $date = date_i18n( 'H:i, j F Y', strtotime( $comment->comment_date ) ); // may output English months; translate if needed
    $content = wp_kses_post( wpautop( $comment->comment_content ) );

    ob_start();
    ?>
    <div class="comment-item" data-comment-id="<?php echo esc_attr( $comment->comment_ID ); ?>">
        <div class="comment-top">
            <div class="user-profile">
                <?php echo $avatar; ?>
                <div class="">
                    <div class="user-name"><?php echo $author_name; ?></div>
                </div>
            </div>
            <p class="date"><?php echo esc_html( $date ); ?></p>
        </div>
        <div class="comment-body">
            <p class="desc"><?php echo $content; ?></p>
        </div>
        <div class="comment-actions">
            <div class="reply">Ответить</div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Toggle like endpoint
 * POST /wp-json/core/v1/toggle-like
 * body: { post_id, post_type }
 */
add_action( 'rest_api_init', function () {
    register_rest_route( 'core/v1', '/toggle-like', [
        'methods'             => 'POST',
        'callback'            => 'core_rest_toggle_like',
        'permission_callback' => function ( $request ) {
            // require logged in
            return is_user_logged_in();
        },
    ] );

    register_rest_route( 'core/v1', '/add-comment', [
        'methods'             => 'POST',
        'callback'            => 'core_rest_add_comment',
        'permission_callback' => function ( $request ) {
            // require logged in to post comments (change if you want guests)
            return is_user_logged_in();
        },
    ] );
} );

/**
 * Toggle like callback
 */
function core_rest_toggle_like( WP_REST_Request $request ) {
    $params = $request->get_json_params();

    $post_id   = isset( $params['post_id'] ) ? intval( $params['post_id'] ) : 0;
    $post_type = isset( $params['post_type'] ) ? sanitize_text_field( $params['post_type'] ) : '';
    $user_id   = get_current_user_id();

    if ( ! $post_id || ! $post_type ) {
        return new WP_REST_Response( [ 'error' => 'Invalid params' ], 400 );
    }

    // meta key to store list of user IDs who liked
    $meta_key = '_core_liked_users';

    $liked_users = get_post_meta( $post_id, $meta_key, true );
    if ( ! is_array( $liked_users ) ) {
        $liked_users = [];
    }

    $liked = false;
    if ( in_array( $user_id, $liked_users, true ) ) {
        // remove like
        $liked_users = array_diff( $liked_users, [ $user_id ] );
        $liked = false;
    } else {
        // add like
        $liked_users[] = $user_id;
        $liked = true;
    }

    // reindex
    $liked_users = array_values( $liked_users );
    update_post_meta( $post_id, $meta_key, $liked_users );

    // update cached like_count meta
    $like_count = count( $liked_users );
    update_post_meta( $post_id, 'like_count', $like_count );

    return rest_ensure_response( [
        'post_id'    => $post_id,
        'liked'      => $liked,
        'like_count' => $like_count,
    ] );
}

/**
 * Add comment callback
 * POST payload:
 * {
 *   post_id: int,
 *   content: string,
 *   parent: int (optional)
 * }
 */
function core_rest_add_comment( WP_REST_Request $request ) {
    $params = $request->get_json_params();

    $post_id = isset( $params['post_id'] ) ? intval( $params['post_id'] ) : 0;
    $content = isset( $params['content'] ) ? wp_kses_post( trim( $params['content'] ) ) : '';
    $parent  = isset( $params['parent'] ) ? intval( $params['parent'] ) : 0;

    if ( ! $post_id || empty( $content ) ) {
        return new WP_REST_Response( [ 'error' => 'Invalid params' ], 400 );
    }

    $current_user = wp_get_current_user();
    if ( ! $current_user || 0 === $current_user->ID ) {
        return new WP_REST_Response( [ 'error' => 'Unauthorized' ], 401 );
    }

    $commentdata = [
        'comment_post_ID'      => $post_id,
        'comment_author'       => $current_user->display_name,
        'comment_author_email' => $current_user->user_email,
        'comment_content'      => $content,
        'user_id'              => $current_user->ID,
        'comment_parent'       => $parent,
        'comment_approved'     => 1, // auto approve; change if moderation needed
    ];

    $comment_id = wp_insert_comment( $commentdata );

    if ( ! $comment_id ) {
        return new WP_REST_Response( [ 'error' => 'Could not insert comment' ], 500 );
    }

    // update comment count meta optionally (WP has comments count builtin)
    $comment_count = get_comments_number( $post_id );

    $comment = get_comment( $comment_id );
    $html = core_render_comment_html( $comment );

    return rest_ensure_response( [
        'success'       => true,
        'comment_id'    => $comment_id,
        'comment_html'  => $html,
        'comment_count' => $comment_count,
    ] );
}


/**
 * Check if current user liked a post
 */
function core_user_liked_post( $post_id ) {
    $user_id = get_current_user_id();
    if ( ! $user_id ) return false;
    $liked_users = get_post_meta( $post_id, '_core_liked_users', true );
    if ( ! is_array( $liked_users ) ) $liked_users = [];
    return in_array( $user_id, $liked_users, true );
}
