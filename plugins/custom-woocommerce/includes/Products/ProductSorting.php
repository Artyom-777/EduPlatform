<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductSorting
{
    /**
     * Register price sorting options.
     */
    public function register_sortable_columns(array $columns): array
    {
        $columns['cwc_price'] = 'cwc_price';

        return $columns;
    }

    /**
     * Modify product query for price sorting.
     */
    public function sort_by_price(\WP_Query $query): void
    {
        if (
            !is_admin() ||
            !$query->is_main_query() ||
            $query->get('post_type') !== 'cwc_product'
        ) {
            return;
        }

        $orderby = $query->get('orderby');

        if ($orderby !== 'cwc_price') {
            return;
        }

        $query->set('meta_key', '_cwc_price');
        $query->set('orderby', 'meta_value_num');
    }
}
