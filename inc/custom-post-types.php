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
}
add_action( 'init', 'core_register_cpts_and_taxes' );
