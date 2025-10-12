<?php
get_header();
?>

    <div class="content">
        <div class="section-top">
            <h2 class="block-title"><?php esc_html_e('Мероприятия', 'core'); ?></h2>
        </div>

        <div class="items-row" id="event-items">
            <?php echo core_event_render_items_html(); ?>
        </div>
    </div>

<?php
get_footer();

