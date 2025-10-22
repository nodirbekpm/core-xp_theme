<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register all CPTs & Taxonomies (RU labels)
 * Slugs: news, events, documents, albums, employees
 * Taxonomies: doc_folder, album_folder, department
 */
function core_register_cpts_and_taxes() {

    /**
     * NEWS (Новости)
     */
    register_post_type( 'news', [
        'labels' => [
            'name'                  => __( 'Новости', 'core' ),
            'singular_name'         => __( 'Новость', 'core' ),
            'menu_name'             => __( 'Новости', 'core' ),
            'name_admin_bar'        => __( 'Новость', 'core' ),
            'add_new'               => __( 'Добавить', 'core' ),
            'add_new_item'          => __( 'Добавить новость', 'core' ),
            'edit_item'             => __( 'Редактировать новость', 'core' ),
            'new_item'              => __( 'Новая новость', 'core' ),
            'view_item'             => __( 'Просмотр новости', 'core' ),
            'search_items'          => __( 'Искать новости', 'core' ),
            'not_found'             => __( 'Новости не найдены', 'core' ),
            'not_found_in_trash'    => __( 'В корзине новостей не найдено', 'core' ),
            'all_items'             => __( 'Все новости', 'core' ),
            'archives'              => __( 'Архив новостей', 'core' ),
        ],
        'public'       => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-megaphone',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions' ],
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'news' ],
    ] );

    /**
     * EVENTS (Мероприятия)
     */
    register_post_type( 'event', [
        'labels' => [
            'name'                  => __( 'Мероприятия', 'core' ),
            'singular_name'         => __( 'Мероприятие', 'core' ),
            'menu_name'             => __( 'Мероприятия', 'core' ),
            'name_admin_bar'        => __( 'Мероприятие', 'core' ),
            'add_new'               => __( 'Добавить', 'core' ),
            'add_new_item'          => __( 'Добавить мероприятие', 'core' ),
            'edit_item'             => __( 'Редактировать мероприятие', 'core' ),
            'new_item'              => __( 'Новое мероприятие', 'core' ),
            'view_item'             => __( 'Просмотр мероприятия', 'core' ),
            'search_items'          => __( 'Искать мероприятия', 'core' ),
            'not_found'             => __( 'Мероприятия не найдены', 'core' ),
            'not_found_in_trash'    => __( 'В корзине мероприятий не найдено', 'core' ),
            'all_items'             => __( 'Все мероприятия', 'core' ),
            'archives'              => __( 'Архив мероприятий', 'core' ),
        ],
        'public'       => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-calendar-alt',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'revisions' ],
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'events' ],
    ] );

    /**
     * DOCUMENTS (Документы)
     */
    register_post_type( 'document', [
        'labels' => [
            'name'                  => __( 'Документы', 'core' ),
            'singular_name'         => __( 'Документ', 'core' ),
            'menu_name'             => __( 'Документы', 'core' ),
            'name_admin_bar'        => __( 'Документ', 'core' ),
            'add_new'               => __( 'Добавить', 'core' ),
            'add_new_item'          => __( 'Добавить документ', 'core' ),
            'edit_item'             => __( 'Редактировать документ', 'core' ),
            'new_item'              => __( 'Новый документ', 'core' ),
            'view_item'             => __( 'Просмотр документа', 'core' ),
            'search_items'          => __( 'Искать документы', 'core' ),
            'not_found'             => __( 'Документы не найдены', 'core' ),
            'not_found_in_trash'    => __( 'В корзине документов не найдено', 'core' ),
            'all_items'             => __( 'Все документы', 'core' ),
            'archives'              => __( 'Архив документов', 'core' ),
        ],
        'public'       => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-media-document',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'revisions' ],
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'documents' ],
    ] );

    /**
     * ALBUMS (Фотоальбомы)
     */
    register_post_type( 'album', [
        'labels' => [
            'name'                  => __( 'Фотоальбомы', 'core' ),
            'singular_name'         => __( 'Альбом', 'core' ),
            'menu_name'             => __( 'Фотоальбомы', 'core' ),
            'name_admin_bar'        => __( 'Альбом', 'core' ),
            'add_new'               => __( 'Добавить', 'core' ),
            'add_new_item'          => __( 'Добавить альбом', 'core' ),
            'edit_item'             => __( 'Редактировать альбом', 'core' ),
            'new_item'              => __( 'Новый альбом', 'core' ),
            'view_item'             => __( 'Просмотр альбома', 'core' ),
            'search_items'          => __( 'Искать альбомы', 'core' ),
            'not_found'             => __( 'Альбомы не найдены', 'core' ),
            'not_found_in_trash'    => __( 'В корзине альбомов не найдено', 'core' ),
            'all_items'             => __( 'Все альбомы', 'core' ),
            'archives'              => __( 'Архив альбомов', 'core' ),
        ],
        'public'       => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-format-gallery',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'comments', 'revisions' ],
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'albums' ],
    ] );

    /**
     * EMPLOYEES (Сотрудники)
     */
    register_post_type( 'employee', [
        'labels' => [
            'name'                  => __( 'Сотрудники', 'core' ),
            'singular_name'         => __( 'Сотрудник', 'core' ),
            'menu_name'             => __( 'Сотрудники', 'core' ),
            'name_admin_bar'        => __( 'Сотрудник', 'core' ),
            'add_new'               => __( 'Добавить', 'core' ),
            'add_new_item'          => __( 'Добавить сотрудника', 'core' ),
            'edit_item'             => __( 'Редактировать сотрудника', 'core' ),
            'new_item'              => __( 'Новый сотрудник', 'core' ),
            'view_item'             => __( 'Просмотр сотрудника', 'core' ),
            'search_items'          => __( 'Искать сотрудников', 'core' ),
            'not_found'             => __( 'Сотрудники не найдены', 'core' ),
            'not_found_in_trash'    => __( 'В корзине сотрудников не найдено', 'core' ),
            'all_items'             => __( 'Все сотрудники', 'core' ),
            'archives'              => __( 'Список сотрудников', 'core' ),
        ],
        'public'       => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-groups',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ],
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'employees' ],
    ] );

    /**
     * STAFFS (Орг структура)
     */
    register_post_type( 'staff', [
        'labels' => [
            'name'                  => __( 'Кадры организации', 'core' ),
            'singular_name'         => __( 'Кадр организации', 'core' ),
            'menu_name'             => __( 'Орг структура', 'core' ),
            'name_admin_bar'        => __( 'Кадр', 'core' ),
            'add_new'               => __( 'Добавить', 'core' ),
            'add_new_item'          => __( 'Добавить кадр', 'core' ),
            'edit_item'             => __( 'Редактировать кадр', 'core' ),
            'new_item'              => __( 'Новый кадр', 'core' ),
            'view_item'             => __( 'Просмотр кадра', 'core' ),
            'search_items'          => __( 'Искать кадры', 'core' ),
            'not_found'             => __( 'Кадры не найдены', 'core' ),
            'not_found_in_trash'    => __( 'В корзине кадров не найдено', 'core' ),
            'all_items'             => __( 'Все кадры', 'core' ),
            'archives'              => __( 'Список кадров', 'core' ),
        ],
        'public'       => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-networking',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'revisions', 'page-attributes' ],
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'staffs' ],
    ] );

    /**
     * VACANCIES (Вакансии)
     */
    register_post_type( 'vacancy', [
        'labels' => [
            'name'                  => __( 'Вакансии', 'core' ),
            'singular_name'         => __( 'Вакансия', 'core' ),
            'menu_name'             => __( 'Вакансии', 'core' ),
            'name_admin_bar'        => __( 'Вакансия', 'core' ),
            'add_new'               => __( 'Добавить', 'core' ),
            'add_new_item'          => __( 'Добавить вакансию', 'core' ),
            'edit_item'             => __( 'Редактировать вакансию', 'core' ),
            'new_item'              => __( 'Новая вакансия', 'core' ),
            'view_item'             => __( 'Просмотр вакансии', 'core' ),
            'search_items'          => __( 'Искать вакансии', 'core' ),
            'not_found'             => __( 'Вакансии не найдены', 'core' ),
            'not_found_in_trash'    => __( 'В корзине вакансий не найдено', 'core' ),
            'all_items'             => __( 'Все вакансии', 'core' ),
            'archives'              => __( 'Архив вакансий', 'core' ),
        ],
        'public'       => true,
        'show_in_rest' => true,
        'menu_icon'    => 'dashicons-id-alt',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'revisions' ],
        'has_archive'  => true,
        'rewrite'      => [ 'slug' => 'vacancies' ],
    ] );

    /**
     * RESUMES (Резюме)
     */
    register_post_type( 'resume', [
        'labels' => [
            'name'               => __( 'Резюме', 'core' ),
            'singular_name'      => __( 'Резюме', 'core' ),
            'menu_name'          => __( 'Резюме', 'core' ),
            'name_admin_bar'     => __( 'Резюме', 'core' ),
            'add_new'            => __( 'Добавить', 'core' ),
            'add_new_item'       => __( 'Добавить резюме', 'core' ),
            'edit_item'          => __( 'Редактировать резюме', 'core' ),
            'new_item'           => __( 'Новое резюме', 'core' ),
            'view_item'          => __( 'Просмотр резюме', 'core' ),
            'search_items'       => __( 'Искать резюме', 'core' ),
            'not_found'          => __( 'Резюме не найдено', 'core' ),
            'not_found_in_trash' => __( 'В корзине резюме не найдено', 'core' ),
            'all_items'          => __( 'Все резюме', 'core' ),
            'archives'           => __( 'Архив резюме', 'core' ),
        ],
        'public'       => false,
        'show_ui'      => true,
        'show_in_menu' => true,
        'menu_icon'    => 'dashicons-id',
        'supports'     => [ 'title', 'custom-fields' ],
    ] );

    /**
     * TAXONOMIES
     */

    // Документы → Папки
    register_taxonomy( 'doc_folder', [ 'document' ], [
        'labels' => [
            'name'              => __( 'Папки документов', 'core' ),
            'singular_name'     => __( 'Папка документов', 'core' ),
            'search_items'      => __( 'Искать папки', 'core' ),
            'all_items'         => __( 'Все папки', 'core' ),
            'parent_item'       => __( 'Родительская папка', 'core' ),
            'parent_item_colon' => __( 'Родительская папка:', 'core' ),
            'edit_item'         => __( 'Редактировать папку', 'core' ),
            'update_item'       => __( 'Обновить папку', 'core' ),
            'add_new_item'      => __( 'Добавить папку', 'core' ),
            'new_item_name'     => __( 'Название новой папки', 'core' ),
            'menu_name'         => __( 'Папки', 'core' ),
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [
            'slug'         => 'documents/folder',
            'hierarchical' => true,
        ],
    ] );

    // Фотоальбомы → Папки
    register_taxonomy( 'album_folder', [ 'album' ], [
        'labels' => [
            'name'              => __( 'Папки альбомов', 'core' ),
            'singular_name'     => __( 'Папка альбомов', 'core' ),
            'search_items'      => __( 'Искать папки', 'core' ),
            'all_items'         => __( 'Все папки', 'core' ),
            'parent_item'       => __( 'Родительская папка', 'core' ),
            'parent_item_colon' => __( 'Родительская папка:', 'core' ),
            'edit_item'         => __( 'Редактировать папку', 'core' ),
            'update_item'       => __( 'Обновить папку', 'core' ),
            'add_new_item'      => __( 'Добавить папку', 'core' ),
            'new_item_name'     => __( 'Название новой папки', 'core' ),
            'menu_name'         => __( 'Папки', 'core' ),
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [
            'slug'         => 'albums/folder',
            'hierarchical' => true,
        ],
    ] );

    // Сотрудники → Отделы
    register_taxonomy( 'department', [ 'employee' ], [
        'labels' => [
            'name'              => __( 'Отделы', 'core' ),
            'singular_name'     => __( 'Отдел', 'core' ),
            'search_items'      => __( 'Искать отделы', 'core' ),
            'all_items'         => __( 'Все отделы', 'core' ),
            'parent_item'       => __( 'Родительский отдел', 'core' ),
            'parent_item_colon' => __( 'Родительский отдел:', 'core' ),
            'edit_item'         => __( 'Редактировать отдел', 'core' ),
            'update_item'       => __( 'Обновить отдел', 'core' ),
            'add_new_item'      => __( 'Добавить отдел', 'core' ),
            'new_item_name'     => __( 'Название нового отдела', 'core' ),
            'menu_name'         => __( 'Отделы', 'core' ),
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [
            'slug'         => 'employees/department',
            'hierarchical' => true,
        ],
    ] );

    // Кадры организации (Орг структура) → Должности
    register_taxonomy( 'position', [ 'staff' ], [
        'labels' => [
            'name'              => __( 'Должности', 'core' ),
            'singular_name'     => __( 'Должность', 'core' ),
            'search_items'      => __( 'Искать должности', 'core' ),
            'all_items'         => __( 'Все должности', 'core' ),
            'parent_item'       => __( 'Родительская должность', 'core' ),
            'parent_item_colon' => __( 'Родительская должность:', 'core' ),
            'edit_item'         => __( 'Редактировать должность', 'core' ),
            'update_item'       => __( 'Обновить должность', 'core' ),
            'add_new_item'      => __( 'Добавить должность', 'core' ),
            'new_item_name'     => __( 'Название новой должности', 'core' ),
            'menu_name'         => __( 'Должности', 'core' ),
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [
            'slug'         => 'staffs/position',
            'hierarchical' => true,
        ],
    ] );

    // Вакансии → Образование
    register_taxonomy( 'education', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'Образование', 'core' ),
            'singular_name' => __( 'Образование', 'core' ),
            'menu_name'     => __( 'Образование', 'core' ),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/education' ],
    ] );

    // Вакансии → Подразделение
    register_taxonomy( 'vac_department', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'Подразделения', 'core' ),
            'singular_name' => __( 'Подразделение', 'core' ),
            'menu_name'     => __( 'Подразделения', 'core' ),
        ],
        'hierarchical'      => true,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/department' ],
    ] );

    // Вакансии → Занятость
    register_taxonomy( 'employment_type', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'Занятость', 'core' ),
            'singular_name' => __( 'Тип занятости', 'core' ),
            'menu_name'     => __( 'Занятость', 'core' ),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/employment' ],
    ] );

    // Вакансии → Опыт работы
    register_taxonomy( 'experience', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'Опыт работы', 'core' ),
            'singular_name' => __( 'Опыт работы', 'core' ),
            'menu_name'     => __( 'Опыт работы', 'core' ),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/experience' ],
    ] );

    // Вакансии → Город
    register_taxonomy( 'city', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'Города', 'core' ),
            'singular_name' => __( 'Город', 'core' ),
            'menu_name'     => __( 'Города', 'core' ),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/city' ],
    ] );

    // Вакансии → Формат работы
    register_taxonomy( 'work_format', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'Формат работы', 'core' ),
            'singular_name' => __( 'Формат работы', 'core' ),
            'menu_name'     => __( 'Формат работы', 'core' ),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/work-format' ],
    ] );

    // Вакансии → График
    register_taxonomy( 'schedule', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'График', 'core' ),
            'singular_name' => __( 'График', 'core' ),
            'menu_name'     => __( 'График', 'core' ),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/schedule' ],
    ] );

    // Вакансии → Гражданство
    register_taxonomy( 'citizenship', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'Гражданство', 'core' ),
            'singular_name' => __( 'Гражданство', 'core' ),
            'menu_name'     => __( 'Гражданство', 'core' ),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/citizenship' ],
    ] );

    // Вакансии → Разрешение на работу
    register_taxonomy( 'work_permission', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'Разрешение на работу', 'core' ),
            'singular_name' => __( 'Разрешение на работу', 'core' ),
            'menu_name'     => __( 'Разрешение на работу', 'core' ),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/work-permission' ],
    ] );

    // Вакансии → Ключевые навыки
    register_taxonomy( 'skills', [ 'vacancy' ], [
        'labels' => [
            'name'          => __( 'Ключевые навыки', 'core' ),
            'singular_name' => __( 'Навык', 'core' ),
            'menu_name'     => __( 'Ключевые навыки', 'core' ),
        ],
        'hierarchical'      => false,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'rewrite'           => [ 'slug' => 'vacancies/skills' ],
    ] );

}
add_action( 'init', 'core_register_cpts_and_taxes' );