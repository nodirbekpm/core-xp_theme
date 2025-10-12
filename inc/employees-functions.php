<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Get hierarchical department terms (top-level with children)
 * returns WP_Term[] with ->children property (may be empty array)
 */
function core_get_department_tree() {
    $terms = get_terms( [
        'taxonomy'   => 'department',
        'hide_empty' => false,
        'parent'     => 0,
        'orderby'    => 'name',
        'order'      => 'ASC',
    ] );
    if ( empty( $terms ) || is_wp_error( $terms ) ) return [];

    foreach ( $terms as $t ) {
        $children = get_terms( [
            'taxonomy'   => 'department',
            'hide_empty' => false,
            'parent'     => $t->term_id,
            'orderby'    => 'name',
        ] );
        $t->children = $children ?: [];
    }
    return $terms;
}

/**
 * Get employees for a given department term_id.
 * If $term_id === 0 -> return all employees (optionally paginated)
 *
 * @param int $term_id
 * @param int $per_page use -1 for all
 * @return WP_Post[]
 */
function core_get_employees_by_department( $term_id = 0, $per_page = -1 ) {
    $args = [
        'post_type'      => 'employee',
        'posts_per_page' => $per_page,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order date',
        'order'          => 'ASC',
    ];

    if ( $term_id ) {
        $args['tax_query'] = [
            [
                'taxonomy'         => 'department',
                'field'            => 'term_id',
                'terms'            => intval( $term_id ),
                'include_children' => true,
            ],
        ];
    }

    $q = new WP_Query( $args );
    return $q->posts;
}

/**
 * Render single department block HTML (title + employees list)
 * uses template-parts/employee-item.php for each employee
 */
function core_render_department_block( $term, $employees ) {
    if ( ! $term ) return '';

    ob_start();
    ?>
    <div class="department-block" data-dept-id="<?php echo esc_attr( $term->term_id ); ?>">
        <div class="subheader department-head" data-dept-id="<?php echo esc_attr( $term->term_id ); ?>">
            <?php echo esc_html( $term->name ); ?>
        </div>

        <div class="documents-row employees-list" data-dept-id="<?php echo esc_attr( $term->term_id ); ?>">
            <?php
            if ( ! empty( $employees ) ) {
                foreach ( $employees as $post ) {
                    $GLOBALS['post'] = $post;
                    setup_postdata( $post );
                    // include template-part (create below)
                    $template = locate_template( 'template-parts/employee-item.php' );
                    if ( $template ) {
                        include $template;
                    } else {
                        // minimal fallback
                        ?>
                        <div class="user-item">
                            <div>
                                <div class="block-title"><?php echo esc_html( get_the_title( $post ) ); ?></div>
                                <div class="user-job"><?php echo esc_html( get_post_meta( $post->ID, 'job_title', true ) ); ?></div>
                            </div>
                        </div>
                        <?php
                    }
                }
                wp_reset_postdata();
            } else {
                // If no employees, we still render an empty wrapper (user requested this)
                // Do not print "not found" text — just keep empty area (or if you prefer, show tiny hint)
                // echo '<p class="no-employees">Нет сотрудников</p>';
            }
            ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * AJAX handler: return department block(s) for a given term_id (0 = all)
 */
add_action( 'wp_ajax_nopriv_core_get_departments_employees_ajax', 'core_get_departments_employees_ajax' );
add_action( 'wp_ajax_core_get_departments_employees_ajax', 'core_get_departments_employees_ajax' );

function core_get_departments_employees_ajax() {
    // security (nonce optional) - we'll accept nonce 'employees_filter'
    if ( isset( $_POST['nonce'] ) ) {
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'employees_filter' ) ) {
            wp_send_json_error( [ 'message' => 'Invalid nonce' ], 403 );
        }
    }

    $term_id = isset( $_POST['term_id'] ) ? intval( $_POST['term_id'] ) : 0;

    // If term_id == 0 -> render all top-level departments (and their children)
    $html = '';

    if ( $term_id === 0 ) {
        $terms = core_get_department_tree();
        if ( ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                // for each top-level term: get employees directly assigned to this term AND its children
                $employees = core_get_employees_by_department( $term->term_id );
                $html .= core_render_department_block( $term, $employees );
            }
        } else {
            // If no departments, we still return empty html (caller will show placeholder)
            $html = '';
        }
    } else {
        // Return single department block
        $term = get_term( $term_id, 'department' );
        if ( ! $term || is_wp_error( $term ) ) {
            wp_send_json_error( [ 'message' => 'Invalid term' ] );
        }
        $employees = core_get_employees_by_department( $term->term_id );
        $html = core_render_department_block( $term, $employees );
    }

    wp_send_json_success( [ 'html' => $html, 'term_id' => $term_id ] );
}
