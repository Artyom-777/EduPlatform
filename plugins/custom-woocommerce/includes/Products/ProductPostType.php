<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductPostType
{
    public function register(): void
    {
        add_filter(
            'use_block_editor_for_post_type',
            [$this, 'disable_block_editor'],
            10,
            2
        );
        
        register_post_type(
            'cwc_product',
            [
                'labels' => [
                    'name'               => __('Products', 'custom-woocommerce'),
                    'singular_name'      => __('Product', 'custom-woocommerce'),
                    'add_new'            => __('Add New', 'custom-woocommerce'),
                    'add_new_item'       => __('Add New Product', 'custom-woocommerce'),
                    'edit_item'          => __('Edit Product', 'custom-woocommerce'),
                    'new_item'           => __('New Product', 'custom-woocommerce'),
                    'view_item'          => __('View Product', 'custom-woocommerce'),
                    'search_items'       => __('Search Products', 'custom-woocommerce'),
                    'not_found'          => __('No products found', 'custom-woocommerce'),
                    'not_found_in_trash' => __('No products found in Trash', 'custom-woocommerce'),
                    'all_items'          => __('All Products', 'custom-woocommerce'),
                ],
                'public'       => true,
                'show_in_rest' => true,
                'has_archive'  => true,
                'rewrite'      => [
                    'slug' => 'shop',
                ],
                'menu_icon'    => 'dashicons-cart',
                'supports'     => [
                    'title',
                    'editor',
                    'thumbnail',
                    'excerpt',
                    'custom-fields',
                ],
            ]
        );
    }
    public function disable_block_editor(
        bool $use_block_editor,
        string $post_type
    ): bool {
        if ($post_type === 'cwc_product') {
            return false;
        }
    
        return $use_block_editor;
    }
}
