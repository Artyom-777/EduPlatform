<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Course Custom Post Type.
 */
function edu_platform_register_course_post_type() {

    $labels = array(
        'name'                  => __( 'Courses', 'edu-platform' ),
        'singular_name'         => __( 'Course', 'edu-platform' ),
        'menu_name'             => __( 'Courses', 'edu-platform' ),
        'add_new'               => __( 'Add New', 'edu-platform' ),
        'add_new_item'          => __( 'Add New Course', 'edu-platform' ),
        'edit_item'             => __( 'Edit Course', 'edu-platform' ),
        'new_item'              => __( 'New Course', 'edu-platform' ),
        'view_item'             => __( 'View Course', 'edu-platform' ),
        'search_items'          => __( 'Search Courses', 'edu-platform' ),
        'not_found'             => __( 'No courses found', 'edu-platform' ),
        'not_found_in_trash'    => __( 'No courses found in Trash', 'edu-platform' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-welcome-learn-more',
        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'author',
        ),
        'has_archive'        => false,
        'rewrite'            => array(
            'slug' => 'course',
        ),
    );

    register_post_type( 'course', $args );
}
add_action( 'init', 'edu_platform_register_course_post_type' );


/**
 * Register Lesson Custom Post Type.
 */
function edu_platform_register_lesson_post_type() {

    $labels = array(
        'name'                  => __( 'Lessons', 'edu-platform' ),
        'singular_name'         => __( 'Lesson', 'edu-platform' ),
        'menu_name'             => __( 'Lessons', 'edu-platform' ),
        'add_new'               => __( 'Add New', 'edu-platform' ),
        'add_new_item'          => __( 'Add New Lesson', 'edu-platform' ),
        'edit_item'             => __( 'Edit Lesson', 'edu-platform' ),
        'new_item'              => __( 'New Lesson', 'edu-platform' ),
        'view_item'             => __( 'View Lesson', 'edu-platform' ),
        'search_items'          => __( 'Search Lessons', 'edu-platform' ),
        'not_found'             => __( 'No lessons found', 'edu-platform' ),
        'not_found_in_trash'    => __( 'No lessons found in Trash', 'edu-platform' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_in_rest'       => true,
        'menu_icon'          => 'dashicons-media-document',
        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
            'page-attributes',
        ),
        'has_archive'        => false,
        'rewrite'            => array(
            'slug' => 'lesson',
        ),
    );

    register_post_type( 'lesson', $args );
}
add_action( 'init', 'edu_platform_register_lesson_post_type' );
