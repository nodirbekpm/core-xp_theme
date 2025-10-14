<?php
get_header();
?>

<?php
// Helper: lavozimlar daraxtini rekursiv render qilish
function render_positions_tree($parent_id = 0) {
    // Shu lavozimning childlarini olamiz
    $positions = get_terms([
        'taxonomy' => 'position',
        'parent'   => $parent_id,
        'hide_empty' => false,
        'orderby' => 'menu_order',
        'order'   => 'ASC'
    ]);

    if (empty($positions) || is_wp_error($positions)) {
        return;
    }

    echo '<ul>';
    foreach ($positions as $position) {
        // Shu lavozimga tegishli xodimlarni olamiz
        $staff_query = new WP_Query([
            'post_type' => 'staff',
            'posts_per_page' => -1,
            'tax_query' => [
                [
                    'taxonomy' => 'position',
                    'field'    => 'term_id',
                    'terms'    => $position->term_id,
                ]
            ]
        ]);

        if ($staff_query->have_posts()) :
            while ($staff_query->have_posts()) : $staff_query->the_post();
                $photo = get_the_post_thumbnail_url(get_the_ID(), 'medium') ?: get_template_directory_uri() . '/assets/img/user-avatar.png';
                ?>
                <li>
                    <div class="person">
                        <img src="<?php echo esc_url($photo); ?>" alt="<?php the_title_attribute(); ?>" class="user-photo">
                        <h3><?php the_title(); ?></h3>
                        <p><?php echo esc_html($position->name); ?></p>
                        <img src="<?php echo get_template_directory_uri(); ?>/assets/img/icons/arr-down2.png" alt="" class="arr-down">
                    </div>

                    <?php
                    // Shu lavozim ostidagi child lavozimlarni ham rekursiv render qilamiz
                    render_positions_tree($position->term_id);
                    ?>
                </li>
            <?php
            endwhile;
            wp_reset_postdata();
        endif;
    }
    echo '</ul>';
}
?>

    <style>
        .content{
            overflow-x: hidden;
        }
    </style>

    <div class="content">
        <ul class="breadcurumbs">
            <li>Оргструктура</li>
        </ul>
        <div class="news-single">
            <div class="org-chart">
                <?php
                /**
                 * Organizational tree renderer for 'staff' CPT and 'position' taxonomy
                 *
                 * Paste this where you want the <ul> tree to appear (template file).
                 */

                /**
                 * Recursive function: render staffs assigned to a given term,
                 * and for each staff render child-terms' staffs inside it.
                 *
                 * @param WP_Term $term
                 * @param array &$rendered_posts  - to avoid duplicate output
                 * @param int $depth
                 * @param int $max_depth
                 */
                function render_staffs_by_term( $term, &$rendered_posts = [], $depth = 0, $max_depth = 10 ) {
                    // safety depth guard
                    if ( $depth > $max_depth ) {
                        return;
                    }

                    // Query staff posts assigned to this term
                    $args = [
                        'post_type'      => 'staff',
                        'posts_per_page' => -1,
                        'post_status'    => 'publish',
                        'orderby'        => 'menu_order title',
                        'order'          => 'ASC',
                        'tax_query'      => [
                            [
                                'taxonomy' => 'position',
                                'field'    => 'term_id',
                                'terms'    => $term->term_id,
                                'include_children' => false, // we handle children explicitly
                            ],
                        ],
                    ];

                    $q = new WP_Query( $args );

                    if ( ! $q->have_posts() ) {
                        wp_reset_postdata();
                        return;
                    }

                    while ( $q->have_posts() ) {
                        $q->the_post();
                        $post_id = get_the_ID();

                        // skip if already rendered (a staff assigned to multiple terms)
                        if ( in_array( $post_id, $rendered_posts, true ) ) {
                            continue;
                        }
                        $rendered_posts[] = $post_id;

                        // featured image or fallback
                        $thumb = get_the_post_thumbnail_url( $post_id, 'thumbnail' );
                        if ( ! $thumb ) {
                            $thumb = get_template_directory_uri() . '/assets/img/user-avatar.png';
                        }

                        // Staff title (name)
                        $staff_name = get_the_title( $post_id );

                        // The position name we are currently in = $term->name
                        $position_name = esc_html( $term->name );

                        ?>
                        <li>
                            <div class="person">
                                <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $staff_name ); ?>" class="user-photo">
                                <h3><?php echo esc_html( $staff_name ); ?></h3>
                                <p><?php echo $position_name; ?></p>
                                <img src="<?php echo esc_url( get_template_directory_uri() . '/assets/img/icons/arr-down2.png' ); ?>" alt="" class="arr-down">
                            </div>

                            <?php
                            // Get child terms of current term (positions under this position)
                            $children = get_terms( [
                                'taxonomy'   => 'position',
                                'hide_empty' => true,
                                'parent'     => $term->term_id,
                            ] );

                            if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
                                echo '<ul>';
                                // For each child term, render the staff assigned to that child term
                                foreach ( $children as $child ) {
                                    // recurse: render staffs for child term (they will in turn check their children)
                                    render_staffs_by_term( $child, $rendered_posts, $depth + 1, $max_depth );
                                }
                                echo '</ul>';
                            }
                            ?>
                        </li>
                        <?php
                    } // end while

                    wp_reset_postdata();
                }

                // --- Usage: render tree starting from top-level position terms (parent = 0) ---
                $top_terms = get_terms( [
                    'taxonomy'   => 'position',
                    'hide_empty' => true,
                    'parent'     => 0,
                ] );

                if ( ! is_wp_error( $top_terms ) && ! empty( $top_terms ) ) {
                    // keep track of already rendered staff IDs (avoid duplicates)
                    $rendered_posts = [];

                    echo '<ul>';
                    foreach ( $top_terms as $term ) {
                        // For each top-level position, render all staffs assigned to it (and their descendants)
                        render_staffs_by_term( $term, $rendered_posts );
                    }
                    echo '</ul>';
                } else {
                    // no top-level positions found
                    echo '<p>No positions found.</p>';
                }
                ?>

            </div>
        </div>
    </div>

<?php
get_footer();

