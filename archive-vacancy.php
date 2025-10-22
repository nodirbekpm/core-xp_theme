<?php
if ( ! is_user_logged_in() ) {
    nocache_headers();
    wp_safe_redirect( home_url('/') );
    exit;
}

get_header();




?>
<div class="tab-menu-wrap vacancy_tab">
    <div class="tab-menu">
        <?php
        $vacancies = new WP_Query(array(
            'post_type' => 'vacancy',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        ));
        if ($vacancies->have_posts()):
            while ($vacancies->have_posts()): $vacancies->the_post();
                $salary_from = get_field('salary_from');
                $salary_to = get_field('salary_to');
                $experience = get_field('experience');

                $experience      = get_the_terms(get_the_ID(), 'experience');
                $experience_text = get_term_names_safe($experience);

                $city      = get_the_terms(get_the_ID(), 'city');
                $city_text = get_term_names_safe($city);
                ?>
                <div class="tab-btn" data-id="<?php echo get_the_ID(); ?>">
                    <a href="?vacancy_id=<?php echo get_the_ID(); ?>" class="tab-head">
                        <div class="job">
                            <div class="job-title"><?php the_title(); ?></div>
                            <div class="job-info">
                                <p class="job-price">
                                    <?php if ($salary_from || $salary_to): ?>
                                        <?php if ($salary_from > 0): ?><?php echo esc_html(format_salary($salary_from)); ?><?php endif; ?>
                                        <?php if ($salary_from > 0 & $salary_to > 0): ?>-<?php endif; ?>
                                        <?php if ($salary_to > 0): ?><?php echo esc_html(format_salary($salary_to)); ?><?php endif; ?>
                                        ₽ за месяц
                                    <?php endif; ?>
                                </p>
                                <?php if ($experience_text): ?>
                                    <span class="tag"><?php echo esc_html($experience_text); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php if ($city_text): ?>
                            <p class="job-location"><?php echo esc_html($city_text); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endwhile;
            wp_reset_postdata();
        endif; ?>
    </div>
</div>

<div class="content vacancy_content">
    <div class="section-top">
        <div class="block-title">Вакансии</div>
    </div>
    <div class="tab-wrap">
        <p>Выберите вакансию, чтобы просмотреть подробности</p>
    </div>
</div>

<!-- Resume Modal -->
<div class="modal-back"></div>
<div class="modal" id="resumeModal">
    <div class="modal-head">
        <div class="block-title">Отправить резюме</div>
        <span class="close-modal">×</span>
    </div>
    <form id="resumeForm" enctype="multipart/form-data">
        <input type="hidden" name="vacancy_id" value="">
        <div class="modal-body">
            <div class="upload-block">
                <label class="upload-input btn-secondary">
                    Выбрать
                    <input type="file" name="file_url" required>
                </label>
                <div class="btn-secondary cancel-btn" disabled>Отмена</div>
                <p class="desc">Перетащите файл для загрузки</p>
            </div>
            <div class="comment-block">
                <div class="comment-title">Комментарий</div>
                <textarea name="comment" placeholder="Введите текст"></textarea>
            </div>
        </div>
        <div class="modal-actions">
            <button class="btn-main close-modal" type="button">Отмена</button>
            <button class="btn-secondary" type="submit">Отправить</button>
        </div>
        <p id="resumeMessage" class="form-message" style="margin-top:10px;"></p>
    </form>
</div>
<?php get_footer(); ?>
