<?php
/**
 * Resume yuborish AJAX funksiyasi
 */
function submit_resume_ajax() {
    check_ajax_referer('resume_nonce', 'nonce');

    $vacancy_id = intval($_POST['vacancy_id']);
    $comment = sanitize_textarea_field($_POST['comment']);

    if (!$vacancy_id) {
        wp_send_json_error('Vacancy ID missing');
    }

    // Faylni yuklash
    if (!empty($_FILES['file']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        // Faylni Media Library’ga yuklaymiz
        $attachment_id = media_handle_upload('file', 0); // 0 = parent hali yo‘q

        if (is_wp_error($attachment_id)) {
            wp_send_json_error('File upload failed');
        }
    } else {
        wp_send_json_error('File missing');
    }

    // Foydalanuvchini olish
    $current_user = wp_get_current_user();

    // Resume post yaratish
    $resume_id = wp_insert_post([
        'post_type'   => 'resume',
        'post_status' => 'publish',
        'post_title'  => 'Резюме от ' . ($current_user->exists() ? $current_user->user_login : 'Гость'),
        'meta_input'  => [
            'comment' => $comment,
        ],
    ]);

    if ($resume_id) {
        // Vacancy (ACF Post Object, return = ID)
        update_post_meta($resume_id, 'vacancy', $vacancy_id);

        // Faylni ACF file field sifatida bog‘lash
        update_field('file_url', $attachment_id, $resume_id);

        // Foydalanuvchi ID saqlash
        update_post_meta($resume_id, 'user_id', $current_user->ID);

        wp_send_json_success('Резюме успешно отправлено!');
    } else {
        wp_send_json_error('Ошибка при сохранении');
    }
}
add_action('wp_ajax_submit_resume', 'submit_resume_ajax');
add_action('wp_ajax_nopriv_submit_resume', 'submit_resume_ajax');



/**
 * 🔧 Admin panelda ustunlar qo‘shish
 */
add_filter('manage_resume_posts_columns', function($columns) {
    $columns['vacancy'] = 'Вакансия';
    $columns['user'] = 'Пользователь';
    $columns['file'] = 'Файл';
    return $columns;
});

/**
 * Ustunlarga ma’lumot chiqarish
 */
add_action('manage_resume_posts_custom_column', function($column, $post_id) {
    if ($column === 'vacancy') {
        $vacancy_id = get_post_meta($post_id, 'vacancy', true);
        if ($vacancy_id) {
            echo '<a href="' . esc_url(get_edit_post_link($vacancy_id)) . '">' . esc_html(get_the_title($vacancy_id)) . '</a>';
        } else {
            echo '—';
        }
    }

    if ($column === 'user') {
        $user_id = get_post_meta($post_id, 'user_id', true);
        if ($user_id) {
            $user = get_userdata($user_id);
            echo esc_html($user->display_name);
        } else {
            echo 'Гость';
        }
    }

    if ($column === 'file') {
        $file = get_field('file_url', $post_id);
        if ($file) {
            $url = is_array($file) ? $file['url'] : wp_get_attachment_url($file);
            echo '<a href="' . esc_url($url) . '" target="_blank">Скачать</a>';
        } else {
            echo '—';
        }
    }
}, 10, 2);



/**
 * 🔍 Admin filter — Vacancy bo‘yicha saralash
 */
add_action('restrict_manage_posts', function($post_type) {
    if ($post_type !== 'resume') return;

    $vacancies = get_posts([
        'post_type' => 'vacancy',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);

    $selected = isset($_GET['filter_vacancy']) ? intval($_GET['filter_vacancy']) : '';

    echo '<select name="filter_vacancy">
            <option value="">Все вакансии</option>';
    foreach ($vacancies as $v) {
        printf(
            '<option value="%d" %s>%s</option>',
            $v->ID,
            selected($selected, $v->ID, false),
            esc_html($v->post_title)
        );
    }
    echo '</select>';
});

/**
 * Filterni so‘rovda qo‘llash
 */
add_filter('parse_query', function($query) {
    global $pagenow;
    if ($pagenow === 'edit.php'
        && isset($_GET['filter_vacancy'])
        && $_GET['filter_vacancy']
        && $query->query_vars['post_type'] === 'resume'
    ) {
        $query->set('meta_query', [
            [
                'key'   => 'vacancy',
                'value' => intval($_GET['filter_vacancy']),
            ]
        ]);
    }
});
