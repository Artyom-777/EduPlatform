<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductSearch
{
    /**
     * Extend product search to include SKU.
     */
    public function search_by_sku(
        string $search,
        \WP_Query $query
    ): string {
        if (
            !is_admin() ||
            !$query->is_main_query() ||
            $query->get('post_type') !== 'cwc_product' ||
            !$query->is_search()
        ) {
            return $search;
        }

        $search_term = $query->get('s');

        if (!$search_term) {
            return $search;
        }

        global $wpdb;

        $like = '%' . $wpdb->esc_like($search_term) . '%';

        $sku_search = $wpdb->prepare(
            " OR EXISTS (
                SELECT 1
                FROM {$wpdb->postmeta} AS sku_meta
                WHERE sku_meta.post_id = {$wpdb->posts}.ID
                AND sku_meta.meta_key = %s
                AND sku_meta.meta_value LIKE %s
            )",
            '_cwc_sku',
            $like
        );

        // Extend the existing WordPress search condition.
        if ($search !== '') {
            $search = preg_replace(
                '/\)\s*$/',
                $sku_search . ')',
                $search,
                1
            );
        }

        return $search;
    }
}


