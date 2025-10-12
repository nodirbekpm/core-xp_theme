<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Yoast SEO asosida custom breadcrumb renderer
 * - Home ni ko‘rsatmaydi
 * - CPTlar ruscha label bilan chiqadi (CPT ro‘yxatida allaqachon ruscha)
 * - Oxirgi bo‘lakni truncation (…)
 */
if ( ! function_exists( 'core_render_breadcrumbs' ) ) {
    function core_render_breadcrumbs( $max_len = 60 ) {
        if ( ! function_exists( 'yoast_breadcrumb' ) ) {
            return;
        }

        // Yoast separatorini noyob token bilan almashtiramiz
        $sep_token  = '|||CORE_BC_SEP|||';
        $sep_filter = function() use ( $sep_token ) { return ' ' . $sep_token . ' '; };
        add_filter( 'wpseo_breadcrumb_separator', $sep_filter, 999 );

        $crumbs_html = yoast_breadcrumb( '', '', false );

        remove_filter( 'wpseo_breadcrumb_separator', $sep_filter, 999 );

        if ( empty( $crumbs_html ) ) {
            return;
        }

        // Plain text bo‘laklar
        $text  = wp_strip_all_tags( $crumbs_html );
        $parts = array_values( array_filter( array_map( 'trim', explode( $sep_token, $text ) ) ) );

        if ( ! $parts ) return;

        // Anchorlar (label -> url)
        $anchors = [];
        if ( preg_match_all( '#<a\s[^>]*href=["\']([^"\']+)["\'][^>]*>(.*?)</a>#iu', $crumbs_html, $m, PREG_SET_ORDER ) ) {
            foreach ( $m as $a ) {
                $anchors[ trim( wp_strip_all_tags( $a[2] ) ) ] = $a[1];
            }
        }

        // Home’ni olib tashlash:
        //  - birinchi label "Home"/"Главная" bo‘lsa
        //  - yoki birinchi URL home_url bo‘lsa
        $first_label = $parts[0] ?? '';
        $first_url   = $anchors[ $first_label ] ?? '';
        $is_home_label = in_array( mb_strtolower( $first_label ), [ 'home', 'главная' ], true );
        $is_home_url   = $first_url && trailingslashit( $first_url ) === trailingslashit( home_url( '/' ) );
        if ( $is_home_label || $is_home_url ) {
            array_shift( $parts );
        }

        if ( ! $parts ) return;

        // Oxirgi bo‘lak index
        $last_index = count( $parts ) - 1;

        // Dizayn strelkasi
        $arrow = esc_url( get_template_directory_uri() . '/assets/img/icons/arr-right.png' );

        // Truncate helper
        $truncate = function( $str ) use ( $max_len ) {
            $s = trim( $str );
            if ( mb_strlen( $s ) <= $max_len ) return $s;
            return rtrim( mb_substr( $s, 0, $max_len ), " \t\n\r\0\x0B.,;:!?" ) . ' …';
        };

        echo '<ul class="breadcurumbs">';

        foreach ( $parts as $i => $label ) {
            $is_last   = ( $i === $last_index );
            $out_label = $is_last ? $truncate( $label ) : $label;
            $url       = $anchors[ $label ] ?? '';

            if ( ! $is_last && ! empty( $url ) ) {
                echo '<li><a href="' . esc_url( $url ) . '">'
                    . esc_html( $out_label )
                    . ' <img src="' . $arrow . '" alt=""></a></li>';
            } else {
                echo '<li>' . esc_html( $out_label ) . '</li>';
            }
        }

        echo '</ul>';
    }
}



// Yoast breadcrumb: CPT arxiv nomlarini ruschaga majburan o'zgartirish
add_filter('wpseo_breadcrumb_links', function ($links) {
    $ru = [
        'news'     => 'Новости',
        'event'    => 'Мероприятия',
        'document' => 'Документы',
        'album'    => 'Фотоальбомы',
        'employee' => 'Сотрудники',
    ];

    foreach ($links as &$link) {
        // Yoast CPT arxiv bo'lagi: ['ptarchive' => '{post_type}']
        if (!empty($link['ptarchive']) && isset($ru[$link['ptarchive']])) {
            $link['text'] = $ru[$link['ptarchive']];
        }
    }
    return $links;
}, 20);
