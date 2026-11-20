<?php
    /**
 * Location Custom Post Type.
 *
 * @package EduPlatform
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Location post type.
 */
function eduplatform_register_location_post_type() {

    $labels = array(
        'name'                  => __( 'Locations', 'edu-platform' ),
        'singular_name'         => __( 'Location', 'edu-platform' ),
        'menu_name'             => __( 'Locations', 'edu-platform' ),
        'name_admin_bar'        => __( 'Location', 'edu-platform' ),
        'add_new'               => __( 'Add New', 'edu-platform' ),
        'add_new_item'          => __( 'Add New Location', 'edu-platform' ),
        'new_item'              => __( 'New Location', 'edu-platform' ),
        'edit_item'             => __( 'Edit Location', 'edu-platform' ),
        'view_item'             => __( 'View Location', 'edu-platform' ),
        'all_items'             => __( 'All Locations', 'edu-platform' ),
        'search_items'          => __( 'Search Locations', 'edu-platform' ),
        'not_found'             => __( 'No locations found.', 'edu-platform' ),
        'not_found_in_trash'    => __( 'No locations found in Trash.', 'edu-platform' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'show_in_rest'       => true,
        'publicly_queryable' => true,
        'query_var'          => true,

        'rewrite'            => array(
            'slug' => 'locations',
        ),

        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,

        'menu_position'      => 9,
        'menu_icon'          => 'dashicons-location-alt',

        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
        ),
    );

    register_post_type( 'location', $args );
}

add_action( 'init', 'eduplatform_register_location_post_type' );
