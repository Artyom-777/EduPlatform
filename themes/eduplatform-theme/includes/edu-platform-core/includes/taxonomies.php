<?php
/**
 * Register Course Category Taxonomy.
 */
function edu_platform_register_course_taxonomy() {

    $labels = array(
        'name'          => __( 'Course Categories', 'edu-platform' ),
        'singular_name' => __( 'Course Category', 'edu-platform' ),
        'search_items'  => __( 'Search Categories', 'edu-platform' ),
        'all_items'     => __( 'All Categories', 'edu-platform' ),
        'edit_item'     => __( 'Edit Category', 'edu-platform' ),
        'add_new_item'  => __( 'Add New Category', 'edu-platform' ),
        'menu_name'     => __( 'Categories', 'edu-platform' ),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => array(
            'slug' => 'course-category',
        ),
    );

    register_taxonomy( 'course_category', array( 'course' ), $args );
}

add_action( 'init', 'edu_platform_register_course_taxonomy' );
