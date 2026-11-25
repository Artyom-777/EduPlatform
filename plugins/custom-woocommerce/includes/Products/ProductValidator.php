<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductValidator
{
    public function validate_sku(string $sku, int $product_id = 0): bool
    {
        global $wpdb;

        if ($sku === '') {
            return true;
        }

        $existing_product_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT post_id
                FROM {$wpdb->postmeta}
                WHERE meta_key = %s
                AND meta_value = %s
                AND post_id != %d
                LIMIT 1",
                '_cwc_sku',
                $sku,
                $product_id
            )
        );

        return empty($existing_product_id);
    }
}
