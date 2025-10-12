<?php
if ( ! is_user_logged_in() ) {
    nocache_headers();
    wp_safe_redirect( home_url('/') );
    exit;
}

get_header();

// include functions file if not already included
// (agar functions`ni functions.php orqali require qilmagan bo'lsang)
// require get_template_directory() . '/includes/employees-functions.php';
?>

<div class="content">
    <div class="section-top">
        <div class="block-title">Сотрудники</div>

        <div class="item-actions">
            <select id="department-filter" class="year-select">
                <option value="0">Все отделы</option>
                <?php
                $top_terms = core_get_department_tree();
                // flatten to options: include children as well
                foreach ( $top_terms as $t ) {
                    echo '<option value="' . esc_attr( $t->term_id ) . '">' . esc_html( $t->name ) . '</option>';
                    if ( ! empty( $t->children ) ) {
                        foreach ( $t->children as $c ) {
                            echo '<option value="' . esc_attr( $c->term_id ) . '">&nbsp;&nbsp;— ' . esc_html( $c->name ) . '</option>';
                        }
                    }
                }
                ?>
            </select>
        </div>
    </div>

    <div id="employees-container">
        <?php
        // initial server render: all top-level department blocks
        $terms = core_get_department_tree();
        if ( ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $employees = core_get_employees_by_department( $term->term_id );
                echo core_render_department_block( $term, $employees );
            }
        } else {
            // no departments — still render empty area (or you can show message)
            echo '<p>' . esc_html__( 'Нет отделов', 'core' ) . '</p>';
        }
        ?>
    </div>
</div>

<?php get_footer(); ?>
