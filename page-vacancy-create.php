<?php
/*
Template Name: Vacancy Create
*/
if (!is_user_logged_in()) {
    wp_safe_redirect(home_url('/'));
    exit;
}
get_header();
?>

<div class="tab-menu-wrap vacancy_create_tab">
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

<div class="content vacancy_create_content">
    <div class="section-top">
        <div class="block-title">Вакансии</div>
        <div class="item-actions">
            <button class="btn-main" form="vacanci-form" type="reset">Отмена</button>
            <button class="btn-secondary" form="vacanci-form" type="submit">Сохранить</button>
        </div>
    </div>

    <form class="vacanci-form" id="vacanci-form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="vacancy_id" value="">

        <div class="form-control">
            <label class='input-wrap'>
                <p class="input-title">Название вакансии <sup>*</sup></p>
                <input type="text" name="vacancy_title" placeholder="Введите" required>
            </label>

            <div class="form-control">
                <label class='input-wrap double-input'>
                    <p class="input-title">Заработная плата</p>
                    <input type="number" placeholder="От" name="salary_from">
                    <input type="number" placeholder="До" name="salary_to">
                </label>

                <label class='input-wrap'>
                    <p class="input-title">Образование <sup>*</sup></p>
                    <select name="education" required>
                        <option value="">Выберите</option>
                        <?php
                        $educations = get_terms(['taxonomy' => 'education', 'hide_empty' => false]);
                        foreach ($educations as $edu) {
                            echo '<option value="' . esc_attr($edu->term_id) . '">' . esc_html($edu->name) . '</option>';
                        }
                        ?>
                    </select>
                    <ul class="choise">
                        <?php foreach ($educations as $edu): ?>
                            <li data-val="<?= esc_attr($edu->term_id) ?>">
                                <?= esc_html($edu->name) ?>
                                <img src="<?= get_template_directory_uri(); ?>/assets/img/icons/cansel.svg" alt="">
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </label>
            </div>
        </div>

        <?php
        $selects = [
            'vac_department' => 'Подразделение',
            'employment_type' => 'Занятость',
            'experience' => 'Опыт работы',
            'city' => 'Город',
            'work_format' => 'Формат работы',
            'schedule' => 'График',
            'work_hours' => 'Рабочие часы',
            'citizenship' => 'Гражданство',
            'work_permission' => 'Разрешение на работу',
            'skills' => 'Ключевые навыки',
        ];

        foreach ($selects as $slug => $label):
            $terms = get_terms(['taxonomy' => $slug, 'hide_empty' => false]);
            if (is_wp_error($terms) || empty($terms)) {
                $terms = [];
            }
            ?>
            <div class="form-control">
            <label class="input-wrap">
                <p class="input-title"><?= esc_html($label) ?> <sup>*</sup></p>
                <select name="<?= esc_attr($slug) . ($slug === 'skills' ? '[]' : '') ?>" <?= $slug === 'skills' ? 'multiple' : '' ?> required>
                    <option value="">Выберите</option>
                    <?php foreach ($terms as $term): ?>
                        <?php if (is_object($term)): ?>
                            <option value="<?= esc_attr($term->term_id) ?>"><?= esc_html($term->name) ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>

                <ul class="choise">
                    <?php foreach ($terms as $term): ?>
                        <?php if (is_object($term)): ?>
                            <li data-val="<?= esc_attr($term->term_id) ?>">
                                <?= esc_html($term->name) ?>
                                <img src="<?= get_template_directory_uri(); ?>/assets/img/icons/cansel.svg" alt="">
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </label>
        </div>
        <?php endforeach; ?>

        <div class="form-control texts">
            <label class='input-wrap'>
                <p class="input-title">Обязанности <sup>*</sup></p>
                <textarea name="responsibilities" placeholder="Введите текст" required></textarea>
            </label>
        </div>

        <div class="form-control">
            <label class='input-wrap'>
                <p class="input-title">Требования <sup>*</sup></p>
                <textarea name="requirements" placeholder="Введите текст" required></textarea>
            </label>
        </div>

        <div class="form-control">
            <label class='input-wrap'>
                <p class="input-title">Условия работы <sup>*</sup></p>
                <textarea name="conditions" placeholder="Введите текст" required></textarea>
            </label>
        </div>
    </form>
</div>



    <script>
        const vacancyAjax = {
            ajaxUrl: "<?php echo admin_url('admin-ajax.php'); ?>",
            nonce: "<?php echo wp_create_nonce('vacancy_nonce'); ?>"
        };
    </script>

    <?php get_footer(); ?>

