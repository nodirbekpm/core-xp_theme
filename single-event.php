<?php
if (!defined('ABSPATH')) {
    exit;
}
get_header();
?>

<?php
function core_format_ru_datetime($datetime_str)
{
    if (empty($datetime_str)) {
        return '';
    }

    $dt = DateTime::createFromFormat('d.m.Y H:i', $datetime_str);
    if (!$dt) {
        $dt = new DateTime($datetime_str);
        if (!$dt) {
            return esc_html($datetime_str);
        }
    }

    $months = [
        1 => 'января',
        2 => 'февраля',
        3 => 'марта',
        4 => 'апреля',
        5 => 'мая',
        6 => 'июня',
        7 => 'июля',
        8 => 'августа',
        9 => 'сентября',
        10 => 'октября',
        11 => 'ноября',
        12 => 'декабря',
    ];

    $H = $dt->format('H');
    $i = $dt->format('i');
    $d = $dt->format('d');
    $m = (int)$dt->format('n');
    $Y = $dt->format('Y');

    $month_name = isset($months[$m]) ? $months[$m] : $dt->format('F');

    return sprintf('%s:%s, %s %s %s', $H, $i, $d, $month_name, $Y);
}

$published_at = get_field('published_at');
$slides = get_field('slides');
$key_topics = get_field('key_topics');
$venue = get_field('venue');
$like_count = (int)get_post_meta(get_the_ID(), 'like_count', true);
$comment_count = get_comments_number(get_the_ID());

if ($like_count < 0) {
    $like_count = 0;
}


// --- START: safe start_time parsing & event date parts ---
$start_time = get_field('start_time'); // ACF field (expected d.m.Y H:i but might be d/m/Y H:i or other)

$event_date_parts = false;

if ( ! empty( $start_time ) ) {
    $dt = false;

    // 1) try exact ACF expected format
    $dt = DateTime::createFromFormat( 'd.m.Y H:i', $start_time );

    // 2) if failed, try replacing slashes with dots (d/m/Y -> d.m.Y)
    if ( ! $dt ) {
        $maybe = str_replace( '/', '.', $start_time );
        $dt = DateTime::createFromFormat( 'd.m.Y H:i', $maybe );
    }

    // 3) if still failed, try some common formats or strtotime fallback inside try/catch
    if ( ! $dt ) {
        try {
            $ts = strtotime( $start_time );
            if ( $ts !== false && $ts !== -1 ) {
                $dt = ( new DateTime() )->setTimestamp( $ts );
            }
        } catch ( Exception $e ) {
            $dt = false;
        }
    }

    if ( $dt && $dt instanceof DateTime ) {
        // nominative months for "февраль 2025"
        $months_nominative = [
            1 => 'январь', 2 => 'февраль', 3 => 'март', 4 => 'апрель',
            5 => 'май', 6 => 'июнь', 7 => 'июль', 8 => 'август',
            9 => 'сентябрь', 10 => 'октябрь', 11 => 'ноябрь', 12 => 'декабрь'
        ];

        $weekdays = [
            'Monday'    => 'понедельник',
            'Tuesday'   => 'вторник',
            'Wednesday' => 'среда',
            'Thursday'  => 'четверг',
            'Friday'    => 'пятница',
            'Saturday'  => 'суббота',
            'Sunday'    => 'воскресенье',
        ];

        $day = $dt->format( 'd' );
        $m = (int) $dt->format( 'n' );
        $Y = $dt->format( 'Y' );

        $month_title = ( isset( $months_nominative[ $m ] ) ? $months_nominative[ $m ] : $dt->format( 'F' ) ) . ' ' . $Y;
        $weekday_en = $dt->format( 'l' );
        $weekday = isset( $weekdays[ $weekday_en ] ) ? $weekdays[ $weekday_en ] : mb_strtolower( $weekday_en );

        $event_date_parts = [
            'day'        => ltrim( $day, '0' ),
            'month_title'=> $month_title,
            'weekday'    => $weekday,
            'datetime'   => $dt,
        ];
    }
}
// --- END: safe start_time parsing & event date parts ---
?>

    <div class="content">
        <?php core_render_breadcrumbs(); ?>

        <div id="post-<?php the_ID(); ?>" data-post-id="<?php the_ID(); ?>"
             data-post-type="<?php echo esc_attr(get_post_type()); ?>" class="news-single">
            <div class="news-top">
                <?php if ($published_at) : ?>
                    <p class="date"><?php echo esc_html(core_format_ru_datetime($published_at)); ?></p>
                <?php else : ?>
                    <p class="date"><?php echo esc_html(get_the_date('H:i, d F Y')); ?></p>
                <?php endif; ?>
                <div class="item-actions">
                    <div class="like <?php echo core_user_liked_post(get_the_ID()) ? 'is-active' : ''; ?>"
                         aria-pressed="false">
                                <span class="like-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="20" viewBox="0 0 22 20" fill="none">
                                        <path d="M19.5166 2.66173C18.9811 2.12039 18.3433 1.691 17.6403 1.39857C16.9372 1.10614 16.183 0.95651 15.4216 0.958392C14.6602 0.95651 13.906 1.10614 13.203 1.39857C12.4999 1.691 11.8621 2.12039 11.3266 2.66173L10.9999 3.00006L10.6733 2.67339C9.59027 1.5905 8.12147 0.982142 6.58994 0.982142C5.05842 0.982142 3.58962 1.5905 2.50661 2.67339C1.43779 3.76381 0.839111 5.22983 0.839111 6.75673C0.839111 8.28362 1.43779 9.74964 2.50661 10.8401L10.4049 18.7617C10.569 18.9256 10.7914 19.0176 11.0233 19.0176C11.2552 19.0176 11.4775 18.9256 11.6416 18.7617L19.5399 10.8401C20.6092 9.74607 21.2059 8.27584 21.2015 6.74612C21.1971 5.2164 20.5921 3.7496 19.5166 2.66173Z" fill="#EF4444"/>
                                    </svg>
                                </span>
                        <span class="count"><?php echo esc_html(get_post_meta(get_the_ID(), 'like_count', true) ?: 0); ?></span>
                    </div>
                    <div class="comment">
                                <span class="comment-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 21 21" fill="none">
                                        <path d="M1.24995 20.625C1.1087 20.6275 0.968963 20.5957 0.842677 20.5323C0.716392 20.469 0.607316 20.3761 0.524771 20.2614C0.442226 20.1468 0.388667 20.0139 0.368672 19.874C0.348677 19.7342 0.36284 19.5915 0.409951 19.4584L2.27662 13.3334C1.87034 12.2802 1.66458 11.1604 1.66995 10.0317C1.66809 8.79003 1.914 7.56047 2.39328 6.41503C2.86343 5.31892 3.53633 4.32144 4.37662 3.47503C5.22108 2.62695 6.22397 1.95307 7.32828 1.4917C8.47036 1.00684 9.69838 0.756958 10.9391 0.756958C12.1799 0.756958 13.4079 1.00684 14.55 1.4917C16.2231 2.20838 17.6502 3.39864 18.6555 4.91599C19.6609 6.43334 20.2006 8.21153 20.2083 10.0317C20.201 12.49 19.2242 14.8461 17.49 16.5884C16.6435 17.4287 15.6461 18.1016 14.55 18.5717C12.344 19.4955 9.8675 19.5373 7.63162 18.6884L1.50662 20.555C1.42469 20.5901 1.33832 20.6136 1.24995 20.625ZM10.9333 2.5417C9.45564 2.54809 8.01215 2.98671 6.78075 3.8035C5.54935 4.62028 4.58386 5.77953 4.00328 7.13836C3.23193 9.00631 3.23193 11.1037 4.00328 12.9717C4.07279 13.1639 4.07279 13.3745 4.00328 13.5667L2.56828 18.4317L7.40995 16.9617C7.6022 16.8922 7.81271 16.8922 8.00495 16.9617C8.92945 17.3455 9.92061 17.5431 10.9216 17.5431C11.9226 17.5431 12.9138 17.3455 13.8383 16.9617C14.7492 16.5782 15.5755 16.0188 16.2698 15.3154C16.9642 14.6121 17.513 13.7786 17.8847 12.8629C18.2564 11.9471 18.4438 10.967 18.4361 9.97865C18.4284 8.99034 18.2259 8.01325 17.84 7.10336C17.2606 5.75113 16.2977 4.59837 15.0701 3.78762C13.8426 2.97687 12.4044 2.54371 10.9333 2.5417Z" fill="#64748B"/>
                                    </svg>
                                </span>
                        <span class="count"><?php echo esc_html(get_comments_number()); ?></span>
                    </div>
                    <a href="<?php echo esc_url( get_permalink() ); ?>"
                       class="btn-main btn-share"
                       data-share-title="<?php echo esc_attr( get_the_title() ); ?>"
                       data-share-text="<?php echo esc_attr( wp_trim_words( get_the_excerpt() ?: get_the_content(), 30, '...' ) ); ?>"
                       data-share-url="<?php echo esc_url( get_permalink() ); ?>">
                        Поделиться
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" fill="none">
                            <path d="M17.3301 0.67008C17.141 0.482247 16.9037 0.350201 16.6443 0.288455C16.385 0.226708 16.1136 0.237651 15.8601 0.32008L1.23012 5.20008C0.958795 5.28606 0.71903 5.45051 0.541111 5.67267C0.363193 5.89484 0.255103 6.16474 0.230492 6.4483C0.205881 6.73186 0.265854 7.01635 0.402836 7.26585C0.539818 7.51534 0.747665 7.71864 1.00012 7.85008L7.07012 10.8501L10.0701 16.9401C10.1907 17.1785 10.3752 17.3786 10.603 17.5181C10.8309 17.6576 11.093 17.731 11.3601 17.7301H11.4601C11.7462 17.709 12.0194 17.6024 12.2441 17.424C12.4688 17.2457 12.6346 17.0039 12.7201 16.7301L17.6701 2.14008C17.7585 1.88801 17.7735 1.61602 17.7133 1.35578C17.6531 1.09553 17.5202 0.857737 17.3301 0.67008ZM1.85012 6.58008L14.6201 2.32008L7.53012 9.41008L1.85012 6.58008ZM11.4301 16.1501L8.59012 10.4701L15.6801 3.38008L11.4301 16.1501Z" fill="#64748B"/>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="events-row">
                <div class="event-info">
                    <h1 class="sectitle2"><?php the_title(); ?></h1>
                    <div class="tags">
                        <?php foreach ($key_topics as $key_topic) : ?>
                        <span class="tag"><?= $key_topic['topic'] ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php if ( $event_date_parts ) : ?>
                        <div class="event-date">
                            <div class="event-day"><?php echo esc_html( ltrim( $event_date_parts['day'], '0' ) ); ?></div>
                            <div>
                                <div class="item-title"><?php echo esc_html( $event_date_parts['month_title'] ); ?></div>
                                <p class="desc"><?php echo esc_html( $event_date_parts['weekday'] ); ?></p>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="event-date">
                            <div class="event-day"><?php echo esc_html( get_the_date('d') ); ?></div>
                            <div>
                                <div class="item-title"><?php echo esc_html( get_the_date('F Y') ); ?></div>
                                <p class="desc"><?php echo esc_html( get_the_date('l') ); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($venue): ?>
                    <p class="event-desc">Место проведения: <?= $venue ?></p>
                    <?php endif; ?>
                    <?php
                    // materials rendering — place where you want the "Получить материалы" button(s) to appear
                    $materials = get_field( 'materials' );

                    if ( $materials && is_array( $materials ) ) :
                        foreach ( $materials as $row ) :

                            // layout key may be 'file' or 'link' per your ACF json
                            if ( isset( $row['acf_fc_layout'] ) && $row['acf_fc_layout'] === 'file' ) {

                                // ACF subfield name 'file' returns array (because return_format = array)
                                $file = isset( $row['file'] ) ? $row['file'] : null;
                                $url  = '';

                                if ( is_array( $file ) && ! empty( $file['url'] ) ) {
                                    $url = $file['url'];
                                } elseif ( ! empty( $file ) && is_numeric( $file ) ) {
                                    // in case file returns attachment ID
                                    $url = wp_get_attachment_url( intval( $file ) );
                                }

                                if ( ! empty( $url ) ) :
                                    // Button text: if file has title use it, otherwise default text
                                    $label = ! empty( $file['title'] ) ? $file['title'] : 'Получить материалы';
                                    ?>
                                    <a href="<?php echo esc_url( $url ); ?>"
                                       class="btn-get"
                                       target="_blank"
                                       rel="noopener noreferrer">
                                        <?php echo esc_html( $label ); ?>
                                    </a>
                                <?php
                                endif;

                            } elseif ( isset( $row['acf_fc_layout'] ) && $row['acf_fc_layout'] === 'link' ) {

                                // ACF 'link' returns array with url, title, target (per your settings)
                                $link = isset( $row['link'] ) ? $row['link'] : null;
                                if ( is_array( $link ) && ! empty( $link['url'] ) ) :
                                    $url    = $link['url'];
                                    $label  = ! empty( $link['title'] ) ? $link['title'] : 'Получить материалы';
                                    $target = ! empty( $link['target'] ) ? $link['target'] : '_self';
                                    $rel    = ( $target === '_blank' ) ? 'noopener noreferrer' : '';
                                    ?>
                                    <a href="<?php echo esc_url( $url ); ?>"
                                       class="btn-get"
                                       target="<?php echo esc_attr( $target ); ?>"
                                       <?php if ( $rel ) : ?>rel="<?php echo esc_attr( $rel ); ?>"<?php endif; ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </a>
                                <?php
                                endif;
                            }

                        endforeach;
                    endif;
                    ?>

                </div>
                <?php
                // get gallery field
                $slides = get_field('slides'); // ACF gallery

                // placeholder in case no image found
                $placeholder = get_template_directory_uri() . '/assets/img/hero/event1.png';

                $img_url = '';
                $img_alt = '';

                // if ACF returns array of image arrays
                if ( is_array( $slides ) && ! empty( $slides[0] ) ) {
                    $first = $slides[0];

                    // case: gallery returns image array (with 'url' and 'alt' keys)
                    if ( is_array( $first ) && ! empty( $first['url'] ) ) {
                        // choose desired size if available, e.g. 'large' or 'full'
                        if ( ! empty( $first['sizes'] ) && ! empty( $first['sizes']['large'] ) ) {
                            $img_url = $first['sizes']['large'];
                        } elseif ( ! empty( $first['url'] ) ) {
                            $img_url = $first['url'];
                        }
                        $img_alt = ! empty( $first['alt'] ) ? $first['alt'] : ( ! empty( $first['title'] ) ? $first['title'] : '' );
                    }
                    // case: gallery returns array of attachment IDs (0 => 123)
                    elseif ( is_numeric( $first ) ) {
                        $att_id = intval( $first );
                        $img_url = wp_get_attachment_image_url( $att_id, 'large' ) ?: wp_get_attachment_url( $att_id );
                        $img_alt = get_post_meta( $att_id, '_wp_attachment_image_alt', true );
                    }
                }

                // final fallback
                if ( empty( $img_url ) ) {
                    $img_url = $placeholder;
                }

                // echo image
                ?>
                <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $img_alt ); ?>" class="event-img">

            </div>
            <div class="sectitle2">О мероприятии</div>
            <style>
                .desc p {
                    margin-bottom: 20px;
                }
                .comments-list{
                    display: flex;
                    flex-direction: column;
                    gap: 20px;
                }
            </style>
            <div class="desc">
                <?php
                the_content();
                ?>
            </div>
        </div>
    </div>

<?php get_footer();
