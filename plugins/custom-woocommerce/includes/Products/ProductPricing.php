<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductPricing
{
  
    public function get_active_price(int $product_id): string
    {
        $regular_price = get_post_meta(
            $product_id,
            '_cwc_regular_price',
            true
        );

        $sale_price = get_post_meta(
            $product_id,
            '_cwc_sale_price',
            true
        );

        $sale_start = get_post_meta(
            $product_id,
            '_cwc_sale_start',
            true
        );

        $sale_end = get_post_meta(
            $product_id,
            '_cwc_sale_end',
            true
        );

        if (
            $regular_price === '' ||
            !is_numeric($regular_price)
        ) {
            return '';
        }

        if (
            $sale_price === '' ||
            !is_numeric($sale_price) ||
            (float) $sale_price >= (float) $regular_price
        ) {
            return $regular_price;
        }

        $now = time();

        if ($sale_start !== '') {
            $start_timestamp = strtotime($sale_start);

            if ($start_timestamp === false || $now < $start_timestamp) {
                return $regular_price;
            }
        }

        if ($sale_end !== '') {
            $end_timestamp = strtotime($sale_end);

            if ($end_timestamp === false || $now > $end_timestamp) {
                return $regular_price;
            }
        }

        return $sale_price;
    }

    public function update_active_price(int $product_id): void
    {
        $active_price = $this->get_active_price($product_id);

        update_post_meta(
            $product_id,
            '_cwc_price',
            $active_price
        );
    }

    public function get_discount_percentage(int $product_id): float
    {
        $regular_price = (float) get_post_meta(
            $product_id,
            '_cwc_regular_price',
            true
        );

        $sale_price = (float) get_post_meta(
            $product_id,
            '_cwc_sale_price',
            true
        );

        if ($regular_price <= 0 || $sale_price <= 0 || $sale_price >= $regular_price) {
            return 0.0;
        }

        $discount = (
            ($regular_price - $sale_price) / $regular_price
        ) * 100;

        return round($discount, 2);
    }
}
