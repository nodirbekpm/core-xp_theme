<?php
// breadcrumbs.php — put this into your theme and call core_render_breadcrumbs() from templates.
// Safe, Yoast-free breadcrumb renderer with truncation and CPT label mapping.

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'core_render_breadcrumbs' ) ) {
    function core_render_breadcrumbs( $max_len = 60 ) {
        // safe mb helpers
        $mb_strlen = function( $s ) { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); };
        $mb_substr = function( $s, $start, $len = null ) {
            if ( function_exists('mb_substr') ) {
                return is_null($len) ? mb_substr($s, $start) : mb_substr($s, $start, $len);
            }
            return is_null($len) ? substr($s, $start) : substr($s, $start, $len);
        };

        // truncate helper
        $truncate = function( $str ) use ( $max_len, $mb_strlen, $mb_substr ) {
            $s = trim( strip_tags( $str ) );
            if ( $mb_strlen($s) <= $max_len ) return $s;
            $cut = rtrim( $mb_substr($s, 0, $max_len), " \t\n\r\0\x0B.,;:!?" );
            return $cut . ' …';
        };

        // CPT archive Russian labels map (same as you used before)
        $ru = [
            'news'     => 'Новости',
            'event'    => 'Мероприятия',
            'document' => 'Документы',
            'album'    => 'Фотоальбомы',
            'employee' => 'Сотрудники',
        ];

        $items = array(); // each item: ['label'=>..., 'url'=>null|string]

        // Helper to add
        $add = function( $label, $url = '' ) use ( & $items ) {
            $items[] = array( 'label' => $label, 'url' => $url );
        };

        // Do not show home; but if you want home, you can add it here:
        // $add( 'Главная', home_url('/') );

        // SINGLE POST (including CPT)
        if ( is_singular() ) {
            $post = get_queried_object();
            // If post type not 'post', link to post type archive
            $pt = get_post_type( $post );
            if ( $pt && $pt !== 'post' ) {
                $archive_link = get_post_type_archive_link( $pt );
                $archive_label = isset($ru[$pt]) ? $ru[$pt] : post_type_archive_title('', false);
                if ( $archive_link ) $add( $archive_label, $archive_link );
            } elseif ( $pt === 'post' ) {
                // regular blog posts -> maybe "Новости" if you map posts to news / or skip
                // if you want to show category trail instead, handle taxonomy below
                $cat = get_the_category( $post->ID );
                if ( ! empty( $cat ) ) {
                    // use first category parent chain
                    $c = $cat[0];
                    $anc = get_ancestors( $c->term_id, 'category', 'taxonomy' );
                    $anc = array_reverse( $anc );
                    foreach ( $anc as $aid ) {
                        $t = get_term( $aid, 'category' );
                        if ( $t && ! is_wp_error( $t ) ) $add( $t->name, get_term_link($t) );
                    }
                    $add( $c->name, get_term_link($c) );
                } else {
                    // optionally add "Блог" archive
                }
            }

            // For hierarchical taxonomies attached to post (e.g. album_folder), show the first taxonomy path if any
            $taxonomies = get_object_taxonomies( $post->post_type, 'names' );
            if ( ! empty( $taxonomies ) ) {
                foreach ( $taxonomies as $tax ) {
                    // skip builtin categories if already handled
                    $terms = wp_get_post_terms( $post->ID, $tax );
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
                        // pick first term
                        $t = $terms[0];
                        // build parent chain
                        if ( is_taxonomy_hierarchical( $tax ) ) {
                            $parents = get_ancestors( $t->term_id, $tax, 'taxonomy' );
                            $parents = array_reverse( $parents );
                            foreach ( $parents as $pid ) {
                                $pt = get_term( $pid, $tax );
                                if ( $pt && ! is_wp_error( $pt ) ) $add( $pt->name, get_term_link($pt) );
                            }
                        }
                        $add( $t->name, get_term_link($t) );
                        break; // only first taxonomy
                    }
                }
            }

            // finally current post (no URL)
            $add( get_the_title( $post ) );
        }
        // TAXONOMY ARCHIVE (category, custom tax)
        elseif ( is_category() || is_tag() || is_tax() ) {
            $term = get_queried_object();
            if ( $term && ! is_wp_error( $term ) ) {
                // if taxonomy has post type archive mapping, show that first? skip for simplicity
                // build parent chain for hierarchical
                if ( is_taxonomy_hierarchical( $term->taxonomy ) ) {
                    $parents = get_ancestors( $term->term_id, $term->taxonomy, 'taxonomy' );
                    $parents = array_reverse( $parents );
                    foreach ( $parents as $pid ) {
                        $pt = get_term( $pid, $term->taxonomy );
                        if ( $pt && ! is_wp_error( $pt ) ) $add( $pt->name, get_term_link($pt) );
                    }
                }
                $add( $term->name );
            }
        }
        // POST TYPE ARCHIVE
        elseif ( is_post_type_archive() ) {
            $pt = get_query_var( 'post_type' );
            if ( is_array( $pt ) ) $pt = reset( $pt );
            $label = isset($ru[$pt]) ? $ru[$pt] : post_type_archive_title('', false);
            $add( $label );
        }
        // AUTHOR
        elseif ( is_author() ) {
            $author = get_queried_object();
            if ( $author ) {
                $add( 'Автор: ' . $author->display_name );
            }
        }
        // DATE (year/month/day)
        elseif ( is_date() ) {
            if ( is_day() ) {
                $add( get_the_date( 'Y' ), get_year_link( get_query_var('year') ) );
                $add( get_the_date( 'F' ), get_month_link( get_query_var('year'), get_query_var('monthnum') ) );
                $add( get_the_date( 'j F Y' ) );
            } elseif ( is_month() ) {
                $add( get_the_date( 'Y' ), get_year_link( get_query_var('year') ) );
                $add( get_the_date( 'F Y' ) );
            } else {
                $add( get_the_date( 'Y' ) );
            }
        }
        // PAGES (hierarchical)
        elseif ( is_page() ) {
            $page = get_queried_object();
            if ( $page ) {
                $parents = get_post_ancestors( $page );
                $parents = array_reverse( $parents );
                foreach ( $parents as $pid ) {
                    $p = get_post( $pid );
                    if ( $p ) $add( get_the_title( $p ), get_permalink($p) );
                }
                $add( get_the_title( $page ) );
            }
        }
        // SEARCH
        elseif ( is_search() ) {
            $add( 'Результаты поиска' );
        }
        // 404
        elseif ( is_404() ) {
            $add( 'Страница не найдена' );
        }
        // Default fallback for archives/home
        else {
            // If it's a generic archive (e.g. taxonomy listing) try to show post type archive
            if ( is_archive() ) {
                $pt = get_query_var('post_type');
                if ( $pt ) {
                    $label = is_array($pt) ? reset($pt) : $pt;
                    $label = isset($ru[$label]) ? $ru[$label] : post_type_archive_title('', false);
                    $add( $label );
                } else {
                    // nothing specific — show site title as only crumb (but requirement was to not show Home)
                    $add( get_bloginfo('name') );
                }
            } else {
                // as fallback show current title if any
                if ( is_singular() ) {
                    $add( get_the_title() );
                }
            }
        }

        // If nothing collected, bail.
        if ( empty( $items ) ) return;

        // Output HTML
        $arrow = esc_url( get_template_directory_uri() . '/assets/img/icons/arr-right.png' );

        echo '<ul class="breadcurumbs">';
        $last_index = count( $items ) - 1;
        foreach ( $items as $i => $it ) {
            $is_last = ( $i === $last_index );
            $label_raw = $it['label'];
            $label_out = $is_last ? $truncate( $label_raw ) : $label_raw;
            $url = ! empty( $it['url'] ) ? $it['url'] : '';

            if ( ! $is_last && $url ) {
                echo '<li><a href="' . esc_url( $url ) . '">' . esc_html( $label_out ) . ' <img src="' . $arrow . '" alt=""></a></li>';
            } else {
                echo '<li>' . esc_html( $label_out ) . '</li>';
            }
        }
        echo '</ul>';
    }
}
