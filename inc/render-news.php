<?php
// Faqat News arxivida JS ulash
add_action( 'wp_enqueue_scripts', function () {
    if ( is_post_type_archive( 'news' ) ) {
        wp_enqueue_script(
            'core-news-archive',
            get_template_directory_uri() . '/assets/js/news-archive.js',
            [ 'jquery' ],
            '1.0.0',
            true
        );
        wp_localize_script( 'core-news-archive', 'CoreNews', [
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'core_news_nonce' ),
        ] );
    }
} );

// AJAX: yil bo‘yicha filtrlash (login bo‘lgan/bo‘lmagan)
add_action( 'wp_ajax_core_filter_news',        'core_ajax_filter_news' );
add_action( 'wp_ajax_nopriv_core_filter_news', 'core_ajax_filter_news' );

function core_ajax_filter_news() {
    check_ajax_referer( 'core_news_nonce', 'nonce' );
    $year = isset( $_POST['year'] ) ? sanitize_text_field( $_POST['year'] ) : 'all';
    $html = core_news_render_items_html( $year );
    wp_send_json_success( [ 'html' => $html ] );
}



// ACF published_at (return: 'd.m.Y H:i') ni timestampga aylantirish + fallback
function core_news_get_published_timestamp( $post_id = null ) {
    $post_id = $post_id ?: get_the_ID();
    $val = get_field( 'published_at', $post_id ); // 'd.m.Y H:i' (masalan: 22.04.2024 19:30)
    if ( $val ) {
        $dt = DateTime::createFromFormat( 'd.m.Y H:i', $val );
        if ( $dt instanceof DateTime ) {
            return $dt->getTimestamp();
        }
    }
    // Fallback: WordPress post vaqti
    return get_post_timestamp( $post_id );
}

// Arxivdagi mavjud yillarning unikal ro‘yxati (desc)
function core_news_get_years() {
    $q = new WP_Query( [
        'post_type'      => 'news',
        'posts_per_page' => -1,
        'fields'         => 'ids',
        'no_found_rows'  => true,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ] );

    $years = [];
    foreach ( $q->posts as $pid ) {
        $years[] = (int) wp_date( 'Y', core_news_get_published_timestamp( $pid ) );
    }
    wp_reset_postdata();
    $years = array_values( array_unique( $years ) );
    rsort( $years );
    return $years;
}

// Yil bo‘yicha (yoki hammasi) itemlar HTML’ini generatsiya qilish
function core_news_render_items_html( $year = null ) {
    ob_start();

    $q = new WP_Query( [
        'post_type'      => 'news',
        'posts_per_page' => -1,
        'orderby'        => 'date', // published_at meta bo‘yicha emas, soddaroq yo‘l
        'order'          => 'DESC',
        'no_found_rows'  => true,
    ] );

    if ( $q->have_posts() ) :
        while ( $q->have_posts() ) : $q->the_post();
            $ts        = core_news_get_published_timestamp();
            $post_year = (int) wp_date( 'Y', $ts );

            // AJAXda kelgan $year filtri bo‘lsa, mos kelmaganini o'tkazib yuboramiz
            if ( $year && 'all' !== $year && (int) $year !== $post_year ) {
                continue;
            }

            $likes           = (int) get_post_meta( get_the_ID(), '_core_likes_count', true );
            $comments_count  = (int) get_comments_number( get_the_ID() );
            $enable_likes    = get_field( 'enable_likes' );    // true/false
            $enable_comments = get_field( 'enable_comments' ); // true/false

            // Excerpt
            $desc = get_the_excerpt();
            if ( ! $desc ) {
                $desc = wp_trim_words( wp_strip_all_tags( get_the_content() ), 24, '…' );
            }
            ?>
            <div class="item news-item" data-year="<?php echo esc_attr( $post_year ); ?>">
                <div class="item-top">
                    <a href="<?php the_permalink(); ?>" class="item-title"><?php the_title(); ?></a>
                    <p class="desc"><?php echo esc_html( $desc ); ?></p>
                </div>
                <div class="item-bottom">
                    <div class="item-actions">
                        <?php if ( $enable_likes ) : ?>
                            <div class="like">
                            <span class="like-icon">
                                <!-- Heart SVG -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="20" viewBox="0 0 22 20" fill="none" aria-hidden="true" focusable="false">
                                    <path d="M19.5166 2.66173C18.9811 2.12039 18.3433 1.691 17.6403 1.39857C16.9372 1.10614 16.183 0.95651 15.4216 0.958392C14.6602 0.95651 13.906 1.10614 13.203 1.39857C12.4999 1.691 11.8621 2.12039 11.3266 2.66173L10.9999 3.00006L10.6733 2.67339C9.59027 1.5905 8.12147 0.982142 6.58994 0.982142C5.05842 0.982142 3.58962 1.5905 2.50661 2.67339C1.43779 3.76381 0.839111 5.22983 0.839111 6.75673C0.839111 8.28362 1.43779 9.74964 2.50661 10.8401L10.4049 18.7617C10.569 18.9256 10.7914 19.0176 11.0233 19.0176C11.2552 19.0176 11.4775 18.9256 11.6416 18.7617L19.5399 10.8401C20.6092 9.74607 21.2059 8.27584 21.2015 6.74612C21.1971 5.2164 20.5921 3.7496 19.5166 2.66173Z" fill="#EF4444"/>
                                </svg>
                            </span>
                                <span class="count"><?php echo esc_html(get_post_meta(get_the_ID(), 'like_count', true) ?: 0); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ( $enable_comments ) : ?>
                            <div class="comment">
                            <span class="comment-icon">
                                <!-- Comment SVG -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21" fill="none" aria-hidden="true" focusable="false">
                                    <path d="M1.24995 20.625C1.1087 20.6275 0.968963 20.5957 0.842677 20.5323C0.716392 20.469 0.607316 20.3761 0.524771 20.2614C0.442226 20.1468 0.388667 20.0139 0.368672 19.874C0.348677 19.7342 0.36284 19.5915 0.409951 19.4584L2.27662 13.3334C1.87034 12.2802 1.66458 11.1604 1.66995 10.0317C1.66809 8.79003 1.914 7.56047 2.39328 6.41503C2.86343 5.31892 3.53633 4.32144 4.37662 3.47503C5.22108 2.62695 6.22397 1.95307 7.32828 1.4917C8.47036 1.00684 9.69838 0.756958 10.9391 0.756958C12.1799 0.756958 13.4079 1.00684 14.55 1.4917C16.2231 2.20838 17.6502 3.39864 18.6555 4.91599C19.6609 6.43334 20.2006 8.21153 20.2083 10.0317C20.201 12.49 19.2242 14.8461 17.49 16.5884C16.6435 17.4287 15.6461 18.1016 14.55 18.5717C12.344 19.4955 9.8675 19.5373 7.63162 18.6884L1.50662 20.555C1.42469 20.5901 1.33832 20.6136 1.24995 20.625ZM10.9333 2.5417C9.45564 2.54809 8.01215 2.98671 6.78075 3.8035C5.54935 4.62028 4.58386 5.77953 4.00328 7.13836C3.23193 9.00631 3.23193 11.1037 4.00328 12.9717C4.07279 13.1639 4.07279 13.3745 4.00328 13.5667L2.56828 18.4317L7.40995 16.9617C7.6022 16.8922 7.81271 16.8922 8.00495 16.9617C8.92945 17.3455 9.92061 17.5431 10.9216 17.5431C11.9226 17.5431 12.9138 17.3455 13.8383 16.9617C14.7492 16.5782 15.5755 16.0188 16.2698 15.3154C16.9642 14.6121 17.513 13.7786 17.8847 12.8629C18.2564 11.9471 18.4438 10.967 18.4361 9.97865C18.4284 8.99034 18.2259 8.01325 17.84 7.10336C17.2606 5.75113 16.2977 4.59837 15.0701 3.78762C13.8426 2.97687 12.4044 2.54371 10.9333 2.5417Z" fill="#64748B"/>
                                </svg>
                            </span>
                                <span class="count"><?php echo esc_html( $comments_count ); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p class="item-date"><?php echo esc_html( wp_date( 'H:i, j F Y', $ts ) ); ?></p>
                </div>
            </div>
        <?php
        endwhile;
    else :
        echo '<p>' . esc_html__( 'Ничего не найдено', 'core' ) . '</p>';
    endif;

    wp_reset_postdata();
    return ob_get_clean();
}
