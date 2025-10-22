<?php
// AJAX: Vacancy detail olish
function get_vacancy_detail_ajax() {
    check_ajax_referer('vacancy_nonce', 'nonce');

    $vacancy_id = intval($_POST['vacancy_id']);
    if (!$vacancy_id) wp_send_json_error('No vacancy id');

    // ACF maydonlarini olish
    $salary_from     = get_field('salary_from', $vacancy_id);
    $salary_to       = get_field('salary_to', $vacancy_id);
    $work_hours      = get_field('work_hours', $vacancy_id);
    $responsibilities = get_field('responsibilities', $vacancy_id);
    $requirements     = get_field('requirements', $vacancy_id);
    $conditions       = get_field('conditions', $vacancy_id);

    // Taxonomiyalarni olish
    $vac_department = get_the_terms($vacancy_id, 'vac_department');
    $employment_type = get_the_terms($vacancy_id, 'employment_type');
    $work_format     = get_the_terms($vacancy_id, 'work_format');
    $experience      = get_the_terms($vacancy_id, 'experience');
    $schedule      = get_the_terms($vacancy_id, 'schedule');

    // Qiymatlarni stringga aylantirish
    $vac_department_text = get_term_names_safe($vac_department);
    $employment_type_text = get_term_names_safe($employment_type);
    $work_format_text     = get_term_names_safe($work_format);
    $experience_text      = get_term_names_safe($experience);
    $schedule_text      = get_term_names_safe($schedule);


    ob_start(); ?>
    <div class="vacancy" data-vacancy-id="<?php echo esc_attr($vacancy_id); ?>">
        <div class="vacancy-main">
            <div class="vacancy-top">
                <div class="vacancy-title"><?php echo esc_html(get_the_title($vacancy_id)); ?></div>

                <?php if ($salary_from || $salary_to): ?>
                    <p class="vacancy-salary">
                        <?php if ($salary_from && $salary_to): ?>
                            от <?php echo esc_html(format_salary($salary_from)); ?> до <?php echo esc_html(format_salary($salary_to)); ?> руб.
                        <?php elseif ($salary_from): ?>
                            от <?php echo esc_html(format_salary($salary_from)); ?> руб.
                        <?php elseif ($salary_to): ?>
                            до <?php echo esc_html(format_salary($salary_to)); ?> руб.
                        <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>

            <div class="job-title"><?php echo esc_html($vac_department_text); ?></div>

            <?php if ($employment_type_text || $work_format_text): ?>
                <div class="job-price">
                    <?php echo esc_html($employment_type_text); ?> занятость
                    <?php if ($employment_type_text && $work_format_text): ?>, <?php endif; ?>
                    <?php echo esc_html($work_format_text); ?>
                </div>
            <?php endif; ?>

            <?php if ($work_hours): ?>
                <div class="job-price">График <?php echo esc_html($schedule_text); ?>, <?php echo esc_html($work_hours); ?> часов</div>
            <?php endif; ?>

            <?php if ($experience_text): ?>
                <span class="tag"><?php echo esc_html($experience_text); ?></span>
            <?php endif; ?>

            <div class="item-actions">
                <a href="#" class="btn-get" data-vacancy="<?php echo esc_attr($vacancy_id); ?>">Отправить резюме</a>
            </div>
        </div>

        <?php if ($responsibilities): ?>
            <div class="job-title">Обязанности</div>
            <p class="desc"><?php echo wp_kses($responsibilities, ['br' => []]); ?></p>
        <?php endif; ?>

        <?php if ($requirements): ?>
            <div class="job-title">Требования</div>
            <p class="desc"><?php echo wp_kses($requirements, ['br' => []]); ?></p>
        <?php endif; ?>

        <?php if ($conditions): ?>
            <div class="job-title">Условия работы</div>
            <p class="desc"><?php echo wp_kses($conditions, ['br' => []]); ?></p>
        <?php endif; ?>
    </div>
    <?php

    $html = ob_get_clean();
    wp_send_json_success(['html' => $html]);
}
add_action('wp_ajax_get_vacancy_detail', 'get_vacancy_detail_ajax');
add_action('wp_ajax_nopriv_get_vacancy_detail', 'get_vacancy_detail_ajax');




// ✅ Vacancy saqlash (create / update)
add_action('wp_ajax_save_vacancy', function () {
    check_ajax_referer('vacancy_nonce', 'nonce');
    parse_str($_POST['data'], $data);

    $title = sanitize_text_field($data['vacancy_title']);
    $id = intval($data['vacancy_id'] ?? 0);

    if ($id) {
        wp_update_post(['ID' => $id, 'post_title' => $title]);
    } else {
        $id = wp_insert_post(['post_type' => 'vacancy', 'post_status' => 'publish', 'post_title' => $title]);
    }

    // Update fields
    update_field('salary_from', $data['salary_from'], $id);
    update_field('salary_to', $data['salary_to'], $id);
    update_field('responsibilities', $data['responsibilities'], $id);
    update_field('requirements', $data['requirements'], $id);
    update_field('conditions', $data['conditions'], $id);

    // Taxonomies
    $taxonomies = ['education','vac_department','employment_type','experience','city','work_format','schedule','work_hours','citizenship','work_permission','skills'];
    foreach ($taxonomies as $tax) {
        if (!empty($data[$tax])) {
            $values = is_array($data[$tax]) ? array_map('intval', $data[$tax]) : [intval($data[$tax])];
            wp_set_post_terms($id, $values, $tax);
        } else {
            wp_set_post_terms($id, [], $tax);
        }
    }

    wp_send_json_success(['id' => $id, 'title' => $title]);
});


add_action('wp_ajax_get_vacancy_detail_full', function () {
    check_ajax_referer('vacancy_nonce', 'nonce');
    $id = intval($_POST['vacancy_id']);
    if (!$id) wp_send_json_error();

    $data = [
        'id' => $id,
        'title' => get_the_title($id),
        'salary_from' => get_field('salary_from', $id),
        'salary_to' => get_field('salary_to', $id),
        'responsibilities' => get_field('responsibilities', $id),
        'requirements' => get_field('requirements', $id),
        'conditions' => get_field('conditions', $id),
        'terms' => []
    ];

    $taxonomies = [
        'education', 'vac_department', 'employment_type', 'experience',
        'city', 'work_format', 'schedule', 'work_hours',
        'citizenship', 'work_permission', 'skills'
    ];

    foreach ($taxonomies as $tax) {
        $terms = get_the_terms($id, $tax);
        if (!empty($terms) && !is_wp_error($terms)) {
            $data['terms'][$tax] = wp_list_pluck($terms, 'term_id');
        } else {
            $data['terms'][$tax] = [];
        }
    }

    wp_send_json_success($data);
});
