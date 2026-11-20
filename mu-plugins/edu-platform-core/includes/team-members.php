<?php
/**
 * Team Member Custom Post Type.
 *
 * @package EduPlatform
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Team Member post type.
 */
function eduplatform_register_team_member_post_type() {

    $labels = array(
        'name'                  => __( 'Team Members', 'edu-platform' ),
        'singular_name'         => __( 'Team Member', 'edu-platform' ),
        'menu_name'             => __( 'Team Members', 'edu-platform' ),
        'name_admin_bar'        => __( 'Team Member', 'edu-platform' ),
        'add_new'               => __( 'Add New', 'edu-platform' ),
        'add_new_item'          => __( 'Add New Team Member', 'edu-platform' ),
        'new_item'              => __( 'New Team Member', 'edu-platform' ),
        'edit_item'             => __( 'Edit Team Member', 'edu-platform' ),
        'view_item'             => __( 'View Team Member', 'edu-platform' ),
        'all_items'             => __( 'All Team Members', 'edu-platform' ),
        'search_items'          => __( 'Search Team Members', 'edu-platform' ),
        'not_found'             => __( 'No team members found.', 'edu-platform' ),
        'not_found_in_trash'    => __( 'No team members found in Trash.', 'edu-platform' ),
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
            'slug' => 'team-members',
        ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 8,
        'menu_icon'          => 'dashicons-groups',
        'supports'           => array(
            'title',
            'editor',
            'thumbnail',
            'excerpt',
        ),
    );

    register_post_type( 'team_member', $args );
}

add_action( 'init', 'eduplatform_register_team_member_post_type' );
