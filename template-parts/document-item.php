<?php
// Ensure we have global $post available and use get_post() fallback
if ( ! isset( $post ) ) {
    global $post;
    if ( ! isset( $post ) ) {
        $post = get_post();
    }
}

if ( ! $post || ! isset( $post->ID ) ) {
    return;
}

$post_id = $post->ID;
$preview = get_field('preview_image', $post_id);
$preview_url = $preview && is_array($preview) ? ( $preview['sizes']['medium'] ?? $preview['url'] ) : get_template_directory_uri() . '/assets/img/document-img.png';
$published_at = get_field('published_at', $post_id);
$author_name = get_field('author_name', $post_id) ?: get_the_author_meta('display_name', $post->post_author);
$file = core_get_document_file_data( $post_id );
$download_url = $file ? $file['url'] : get_permalink( $post_id );
$download_label = $file ? $file['label'] : __('Open', 'core');
?>
<div class="document-item" data-post-id="<?php echo esc_attr($post_id); ?>">
    <img src="<?php echo esc_url( $preview_url ); ?>" alt="" class="document-img">
    <div class="document-right">
        <div class="item-title"><?php echo esc_html( get_the_title( $post_id ) ); ?></div>
        <div class="document-texts">
            <span class="event-desc"><?php echo esc_html( $published_at ? $published_at : get_the_date( 'H:i, j F Y', $post_id ) ); ?></span>
            <span class="doc-user-name"><?php echo esc_html( $author_name ); ?></span>
        </div>
        <a href="<?php echo esc_url( $download_url ); ?>" class="btn-main btn-download" target="_blank" rel="noopener noreferrer" data-post-id="<?php echo esc_attr($post_id); ?>">
<!--            --><?php //echo esc_html( $download_label ); ?>
            Скачать
        </a>
    </div>
</div>
