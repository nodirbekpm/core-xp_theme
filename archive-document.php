<?php
if ( ! is_user_logged_in() ) {
    nocache_headers();
    wp_safe_redirect( home_url('/') );
    exit;
}

get_header();

// olamiz papkalar daraxtini
$folders = core_get_doc_folders_tree();

// tanlangan papka: GET param (folder) yoki 0 (ya'ni hech qanday default)
// IMPORTANT: we intentionally do NOT fallback to first folder to prevent initial load.
$selected_folder_id = 0;
if ( isset( $_GET['folder_id'] ) && intval( $_GET['folder_id'] ) ) {
    $selected_folder_id = intval( $_GET['folder_id'] );
}

// Helper: agar selected child bo'lsa parentni topib uni ochish va childga ham flag qo'yish
if ( $selected_folder_id && ! empty( $folders ) ) {
    // reset flags first
    foreach ( $folders as $folder ) {
        $folder->is_open = false;
        if ( ! empty( $folder->children ) ) {
            foreach ( $folder->children as $child ) {
                $child->is_open = false;
            }
        }
    }

    // qidirish va flag qo'yish
    foreach ( $folders as $folder ) {
        if ( $folder->term_id == $selected_folder_id ) {
            $folder->is_open = true;
            break;
        }
        if ( ! empty( $folder->children ) ) {
            foreach ( $folder->children as $child ) {
                if ( $child->term_id == $selected_folder_id ) {
                    $folder->is_open = true;   // och parent
                    $child->is_open  = true;   // childni ham belgilash
                    break 2;
                }
            }
        }
    }
}

// Agar folder_id berilgan bo'lsa, initial documents ni oling — aks holda bo'sh qoldiring.
// (JS sahifa yuklanganda agar kerak bo'lsa AJAX orqali yuklaydi.)
$initial_docs = array();
if ( $selected_folder_id ) {
    $initial_docs = core_get_documents_by_folder( $selected_folder_id );
}
?>

    <div class="tab-menu-wrap">
        <div class="tab-menu" id="doc-folders" <?php if ( $selected_folder_id ) : ?>data-selected="<?php echo esc_attr( $selected_folder_id ); ?>"<?php endif; ?>>
            <?php if ( empty($folders) ) : ?>
                <p><?php esc_html_e('No folders', 'core'); ?></p>
            <?php else : ?>
                <?php foreach ( $folders as $folder ) :
                    $children = $folder->children;
                    $is_open = ! empty( $folder->is_open ) ? ' is-open' : '';
                    // top-level active if selected equals folder or one of its children
                    $is_active = (int)$folder->term_id === (int)$selected_folder_id ? ' active-folder' : '';
                    // if child selected, parent should also be active-folder for styling
                    if ( ! empty( $children ) ) {
                        foreach ( $children as $child ) {
                            if ( (int)$child->term_id === (int)$selected_folder_id ) {
                                $is_active = ' active-folder';
                            }
                        }
                    }
                    ?>
                    <div class="tab-btn<?php echo $is_open . $is_active; ?>" data-folder-id="<?php echo esc_attr( $folder->term_id ); ?>">
                        <a href="#" class="tab-head" data-term-link="<?php echo esc_attr( get_term_link( $folder ) ); ?>" data-href="<?php echo esc_url( add_query_arg( 'folder_id', $folder->term_id, get_post_type_archive_link('document') ) ); ?>">
                            <span class="tab-arr">
                                <img src="<?php echo get_template_directory_uri() ?>/assets/img/icons/arr-right2.svg" alt="">
                            </span>
                            <img src="<?php echo get_template_directory_uri() ?>/assets/img/icons/papka-icon.png" alt="">
                            <span><?php echo esc_html( $folder->name ); ?></span>
                        </a>
                        <div class="inner-menu">
                            <?php if ( ! empty( $children ) ) : ?>
                                <?php foreach ( $children as $child ) :
                                    $child_active = ! empty( $child->is_open ) ? ' is-open active-folder' : ( (int)$child->term_id === (int)$selected_folder_id ? ' active-folder' : '' );
                                    ?>
                                    <div class="tab-btn<?php echo $child_active; ?>" data-folder-id="<?php echo esc_attr( $child->term_id ); ?>">
                                        <a href="#" class="tab-head" data-term-link="<?php echo esc_attr( get_term_link( $child ) ); ?>" data-href="<?php echo esc_url( add_query_arg( 'folder_id', $child->term_id, get_post_type_archive_link('document') ) ); ?>">
                                            <span><?php echo esc_html( $child->name ); ?></span>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="content">
        <div class="section-top">
            <div class="subheader">
                <img src="<?php echo get_template_directory_uri() ?>/assets/img/icons/file-icon.png" alt="">
                Документы
            </div>
        </div>

        <div class="tab-wrap" id="doc-content">
            <div class="tab-item">
                <div class="documents-row" id="documents-list">
                    <?php
                    // Agar server tomondan selected_folder_id berilgan bo'lsa, u holda
                    // biz serverda shu folderga mos documentlarni chiqaramiz (fallback).
                    // Agar selected_folder_id yo'q bo'lsa — bo'sh qoldiramiz (JS yuklaydi).
                    if ( $selected_folder_id && ! empty( $initial_docs ) ) :
                        foreach ( $initial_docs as $post ) :
                            setup_postdata( $post );
                            get_template_part( 'template-parts/document-item' );
                        endforeach;
                        wp_reset_postdata();
                    else :
                        // Agar siz sahifa yuklanganda hech qanday xabar ko'rinmasin desa, quyidagi qatorni o'chiring yoki commented qoldiring.
                        // hozircha hech narsa chiqarilmaydi — izoh: agar server tanlagan folder bo'lmasa, content bo'sh bo'ladi.
                        // echo '<p>' . esc_html__( 'Выберите папку, чтобы увидеть документы', 'core' ) . '</p>';
                    endif;
                    ?>
                </div>

            </div>
        </div>
    </div>

<?php
get_footer();
