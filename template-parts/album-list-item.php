<?php
if ( ! isset($post) ) return;
$post_id = $post->ID;
?>
<div class="tab-btn album-item" data-album-id="<?php echo esc_attr( $post_id ); ?>">
    <a href="#" class="tab-head" data-album-link="<?php echo esc_attr( get_permalink( $post_id ) ); ?>">
        <span><?php echo esc_html( get_the_title( $post ) ); ?></span>
    </a>
</div>
