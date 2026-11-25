<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductAdminFilters
{
    /**
     * Display stock status filter.
     */
    public function render_stock_filter(): void
    {
        global $typenow;

        if ($typenow !== 'cwc_product') {
            return;
        }

        $selected = isset($_GET['cwc_stock_filter'])
            ? sanitize_text_field(wp_unslash($_GET['cwc_stock_filter']))
            : '';

        $statuses = [
            'instock'     => __('In stock', 'custom-woocommerce'),
            'outofstock'  => __('Out of stock', 'custom-woocommerce'),
            'onbackorder' => __('On backorder', 'custom-woocommerce'),
        ];

        echo '<select name="cwc_stock_filter">';
        echo '<option value="">' . esc_html__('All stock statuses', 'custom-woocommerce') . '</option>';

        foreach ($statuses as $value => $label) {
            printf(
                '<option value="%s" %s>%s</option>',
                esc_attr($value),
                selected($selected, $value, false),
                esc_html($label)
            );
        }

        echo '</select>';
    }

    /**
     * Apply stock status filter.
     */
    public function filter_products_by_stock(\WP_Query $query): void
    {
        if (
            !is_admin() ||
            !$query->is_main_query() ||
            $query->get('post_type') !== 'cwc_product' ||
            empty($_GET['cwc_stock_filter'])
        ) {
            return;
        }

        $status = sanitize_text_field(
            wp_unslash($_GET['cwc_stock_filter'])
        );

        $allowed_statuses = [
            'instock',
            'outofstock',
            'onbackorder',
        ];

        if (!in_array($status, $allowed_statuses, true)) {
            return;
        }

        $meta_query = $query->get('meta_query') ?: [];

        $meta_query[] = [
            'key'     => '_cwc_stock_status',
            'value'   => $status,
            'compare' => '=',
        ];

        $query->set('meta_query', $meta_query);
    }
}
