<?php
// 2.1 — Sidebar uchun maxsus image size (kvadrat, qirqib beradi)
add_action( 'after_setup_theme', function () {
    add_image_size( 'sidebar-photo', 320, 320, true ); // kerak bo‘lsa o‘lchamni o‘zgartirasan
} );

/**
 * 2.2 — User Profile sahifasiga "Sidebar Photo" upload field
 */
add_action( 'show_user_profile', 'core_sidebar_photo_field' );
add_action( 'edit_user_profile',  'core_sidebar_photo_field' );
function core_sidebar_photo_field( $user ) {
    $meta_key  = 'core_sidebar_photo_id';
    $attach_id = (int) get_user_meta( $user->ID, $meta_key, true );
    $url       = $attach_id ? wp_get_attachment_image_url( $attach_id, 'sidebar-photo' ) : '';
    ?>
    <h2><?php esc_html_e( 'Sidebar Photo', 'core' ); ?></h2>
    <table class="form-table" role="presentation">
        <tr>
            <th><label for="core_sidebar_photo"><?php esc_html_e( 'Image', 'core' ); ?></label></th>
            <td>
                <?php if ( $url ): ?>
                    <div style="margin-bottom:10px;">
                        <img src="<?php echo esc_url( $url ); ?>" alt="" style="width:96px;height:96px;border-radius:50%;object-fit:cover;">
                    </div>
                <?php endif; ?>
                <input type="file" name="core_sidebar_photo" id="core_sidebar_photo" accept="image/*" />
                <?php if ( $attach_id ): ?>
                    <p><label><input type="checkbox" name="core_sidebar_photo_remove" value="1"> <?php esc_html_e( 'Remove current image', 'core' ); ?></label></p>
                <?php endif; ?>
                <?php wp_nonce_field( 'core_sidebar_photo_nonce', 'core_sidebar_photo_nonce' ); ?>
                <p class="description"><?php esc_html_e( 'Upload JPG/PNG (recommended 320×320 or larger).', 'core' ); ?></p>
            </td>
        </tr>
    </table>
    <?php
}

// 2.3 — Saqlash (upload -> media -> user_meta)
add_action( 'personal_options_update', 'core_sidebar_photo_save' );
add_action( 'edit_user_profile_update', 'core_sidebar_photo_save' );
function core_sidebar_photo_save( $user_id ) {
    if ( ! current_user_can( 'edit_user', $user_id ) ) return;
    if ( empty( $_POST['core_sidebar_photo_nonce'] ) || ! wp_verify_nonce( $_POST['core_sidebar_photo_nonce'], 'core_sidebar_photo_nonce' ) ) return;

    $meta_key = 'core_sidebar_photo_id';

    // remove requested
    if ( ! empty( $_POST['core_sidebar_photo_remove'] ) ) {
        delete_user_meta( $user_id, $meta_key );
    }

    // new upload?
    if ( ! empty( $_FILES['core_sidebar_photo']['name'] ) ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        $uploaded = wp_handle_upload( $_FILES['core_sidebar_photo'], [ 'test_form' => false ] );
        if ( empty( $uploaded['error'] ) ) {
            $attachment_id = wp_insert_attachment( [
                'post_mime_type' => $uploaded['type'],
                'post_title'     => wp_basename( $uploaded['file'] ),
                'post_content'   => '',
                'post_status'    => 'inherit',
            ], $uploaded['file'] );

            if ( ! is_wp_error( $attachment_id ) ) {
                $meta = wp_generate_attachment_metadata( $attachment_id, $uploaded['file'] );
                wp_update_attachment_metadata( $attachment_id, $meta );
                update_user_meta( $user_id, $meta_key, (int) $attachment_id );
            }
        }
    }
}

/**
 * 2.4 — Qulay helper: sidebar rasmi HTML (fallback: avatar)
 * $user_id: ko‘rsatmoqchi bo‘lgan user ID (default: current)
 */
function core_get_sidebar_photo_html( $user_id = 0, $attrs = [] ) {
    $user_id   = $user_id ?: get_current_user_id();
    $attach_id = (int) get_user_meta( $user_id, 'core_sidebar_photo_id', true );

    $defaults = [ 'class' => 'sidebar-photo', 'loading' => 'lazy', 'decoding' => 'async' ];
    $attrs    = array_merge( $defaults, $attrs );

    if ( $attach_id ) {
        return wp_get_attachment_image( $attach_id, 'sidebar-photo', false, $attrs );
    }
    // Fallback — kattaroq avatar so‘raymiz (retina uchun 192/256)
    return get_avatar( $user_id, 192, '', '', $attrs );
}
