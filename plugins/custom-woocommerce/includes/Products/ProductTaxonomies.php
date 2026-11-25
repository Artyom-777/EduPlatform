<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductTaxonomies
{
    public function register(): void
    {
        $this->register_product_categories();

        $this->register_product_tags();
    }

    private function register_product_categories(): void
    {
        register_taxonomy(
            'cwc_product_cat',
            ['cwc_product'],
            [
                'labels' => [
                    'name'              => __('Product Categories', 'custom-woocommerce'),
                    'singular_name'     => __('Product Category', 'custom-woocommerce'),
                    'search_items'      => __('Search Categories', 'custom-woocommerce'),
                    'all_items'         => __('All Categories', 'custom-woocommerce'),
                    'parent_item'       => __('Parent Category', 'custom-woocommerce'),
                    'parent_item_colon' => __('Parent Category:', 'custom-woocommerce'),
                    'edit_item'         => __('Edit Category', 'custom-woocommerce'),
                    'update_item'       => __('Update Category', 'custom-woocommerce'),
                    'add_new_item'      => __('Add New Category', 'custom-woocommerce'),
                    'new_item_name'     => __('New Category Name', 'custom-woocommerce'),
                    'menu_name'         => __('Categories', 'custom-woocommerce'),
                ],
                'hierarchical'      => true,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => [
                    'slug' => 'product-category',
                ],
            ]
        );
    }

    private function register_product_tags(): void
    {
        register_taxonomy(
            'cwc_product_tag',
            ['cwc_product'],
            [
                'labels' => [
                    'name'          => __('Product Tags', 'custom-woocommerce'),
                    'singular_name' => __('Product Tag', 'custom-woocommerce'),
                    'search_items'  => __('Search Tags', 'custom-woocommerce'),
                    'all_items'     => __('All Tags', 'custom-woocommerce'),
                    'edit_item'     => __('Edit Tag', 'custom-woocommerce'),
                    'update_item'   => __('Update Tag', 'custom-woocommerce'),
                    'add_new_item'  => __('Add New Tag', 'custom-woocommerce'),
                    'new_item_name' => __('New Tag Name', 'custom-woocommerce'),
                    'menu_name'     => __('Tags', 'custom-woocommerce'),
                ],
                'hierarchical'      => false,
                'public'            => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'show_in_rest'      => true,
                'rewrite'           => [
                    'slug' => 'product-tag',
                ],
            ]
        );
    }
}
