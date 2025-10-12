<?php
get_header();
?>

    <div class="content">
        <div class="section-top">
            <h2 class="block-title"><?php esc_html_e('Новости', 'core'); ?></h2>

            <select class="year-select" data-sort="1">
                <option value="all"><?php esc_html_e('Все годы', 'core'); ?></option>
                <?php foreach ( core_news_get_years() as $year ) : ?>
                    <option value="<?php echo esc_attr( $year ); ?>"><?php echo esc_html( $year ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="items-row" data-target="1" id="news-items">
            <?php echo core_news_render_items_html(); ?>
        </div>
    </div>

<?php
get_footer();

