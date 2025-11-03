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
        <input type="hidden" name="vacancy_id">
        <div class="form-control">
            <label class='input-wrap'>
                <p class="input-title">Название вакансии <sup>*</sup></p>
                <input type="text" name="vacancy_title" placeholder="Введите" required>
            </label>

            <div class="form-control">
                <label class='input-wrap double-input'>
                    <p class="input-title">Заработная плата</p>
                    <input type="number" name="salary_from" placeholder="От">
                    <input type="number" name="salary_to" placeholder="До">
                </label>

                <label class='input-wrap'>
                    <p class="input-title">Образование <sup>*</sup></p>
                    <select name="education" required>
                        <?php
                        $educations = get_terms(['taxonomy' => 'education', 'hide_empty' => false]);
                        foreach ($educations as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                        }
                        ?>
                    </select>
                </label>
            </div>
        </div>

        <div class="form-control">
            <label class='input-wrap'>
                <p class="input-title">Подразделение <sup>*</sup></p>
                <select name="vac_department" required>
                    <?php
                    $departments = get_terms(['taxonomy' => 'vac_department', 'hide_empty' => false]);
                    foreach ($departments as $term) {
                        echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                    }
                    ?>
                </select>
            </label>

            <div class="form-control">
                <label class='input-wrap'>
                    <p class="input-title">Занятость <sup>*</sup></p>
                    <select name="employment_type" required>
                        <?php
                        $employment = get_terms(['taxonomy' => 'employment_type', 'hide_empty' => false]);
                        foreach ($employment as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                        }
                        ?>
                    </select>
                </label>

                <label class='input-wrap'>
                    <p class="input-title">Опыт работы <sup>*</sup></p>
                    <select name="experience" required>
                        <?php
                        $experience = get_terms(['taxonomy' => 'experience', 'hide_empty' => false]);
                        foreach ($experience as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                        }
                        ?>
                    </select>
                </label>
            </div>
        </div>

        <div class="form-control">
            <label class='input-wrap'>
                <p class="input-title">Город <sup>*</sup></p>
                <select name="city" required>
                    <?php
                    $cities = get_terms(['taxonomy' => 'city', 'hide_empty' => false]);
                    foreach ($cities as $term) {
                        echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                    }
                    ?>
                </select>
                <ul class="choise">
                    <?php
                    $cities = get_terms(['taxonomy' => 'city', 'hide_empty' => false]);
                    foreach ($cities as $term) {
                        echo '<li value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '<img src="<?php echo get_template_directory_uri() ?>/assets/img/icons/cansel.svg" alt=""></li>';
                    }
                    ?>
                </ul>
            </label>

            <div class="form-control">
                <label class='input-wrap'>
                    <p class="input-title">Формат работы <sup>*</sup></p>
                    <select name="work_format" required>
                        <?php
                        $formats = get_terms(['taxonomy' => 'work_format', 'hide_empty' => false]);
                        foreach ($formats as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                        }
                        ?>
                    </select>
                </label>

                <label class='input-wrap'>
                    <p class="input-title">График <sup>*</sup></p>
                    <select name="schedule" required>
                        <?php
                        $schedules = get_terms(['taxonomy' => 'schedule', 'hide_empty' => false]);
                        foreach ($schedules as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                        }
                        ?>
                    </select>
                </label>

                <label class='input-wrap'>
                    <p class="input-title">Рабочие часы <sup>*</sup></p>
                    <select name="work_hours" required>
                        <?php
                        $schedules = get_terms(['taxonomy' => 'work_hours', 'hide_empty' => false, 'orderby' => 'term_id']);
                        foreach ($schedules as $term) {
                            echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                        }
                        ?>
                    </select>
                </label>
            </div>
        </div>

        <div class="form-control">
            <label class='input-wrap'>
                <p class="input-title">Гражданство <sup>*</sup></p>
                <select name="citizenship" required>
                    <?php
                    $citizenship = get_terms(['taxonomy' => 'citizenship', 'hide_empty' => false]);
                    foreach ($citizenship as $term) {
                        echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                    }
                    ?>
                </select>
                <ul class="choise">
                    <?php
                    $citizenship = get_terms(['taxonomy' => 'citizenship', 'hide_empty' => false]);
                    foreach ($citizenship as $term) {
                        echo '<li value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '<img src="<?php echo get_template_directory_uri() ?>/assets/img/icons/cansel.svg" alt=""></li>';
                    }
                    ?>
                </ul>
            </label>

            <label class='input-wrap'>
                <p class="input-title">Разрешение на работу <sup>*</sup></p>
                <select name="work_permission" required>
                    <?php
                    $permission = get_terms(['taxonomy' => 'work_permission', 'hide_empty' => false]);
                    foreach ($permission as $term) {
                        echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                    }
                    ?>
                </select>
                <ul class="choise">
                    <?php
                    $permission = get_terms(['taxonomy' => 'work_permission', 'hide_empty' => false]);
                    foreach ($permission as $term) {
                        echo '<li value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '<img src="<?php echo get_template_directory_uri() ?>/assets/img/icons/cansel.svg" alt=""></li>';
                    }
                    ?>
                </ul>
            </label>
        </div>

        <div class="form-control">
            <label class='input-wrap'>
                <p class="input-title">Ключевые навыки <sup>*</sup></p>
                <select name="skills" required>
                    <?php
                    $skills = get_terms(['taxonomy' => 'skills', 'hide_empty' => false]);
                    foreach ($skills as $term) {
                        echo '<option value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '</option>';
                    }
                    ?>
                </select>
                <ul class="choise">
                    <?php
                    $skills = get_terms(['taxonomy' => 'skills', 'hide_empty' => false]);
                    foreach ($skills as $term) {
                        echo '<li value="' . esc_attr($term->term_id) . '">' . esc_html($term->name) . '<img src="<?php echo get_template_directory_uri() ?>/assets/img/icons/cansel.svg" alt=""></li>';
                    }
                    ?>
                </ul>
            </label>
        </div>

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

