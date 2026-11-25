<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductSaleScheduler
{
    private const CRON_HOOK = 'cwc_refresh_product_price';

    /**
     * Schedule product sale start and end events.
     */
    public function schedule(int $product_id): void
    {
        // Remove previously scheduled events.
        wp_clear_scheduled_hook(
            self::CRON_HOOK,
            [$product_id]
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

        $start_timestamp = $this->get_timestamp($sale_start);
        $end_timestamp = $this->get_timestamp($sale_end);

        // Schedule sale start.
        if (
            $start_timestamp !== false &&
            $start_timestamp > time()
        ) {
            wp_schedule_single_event(
                $start_timestamp,
                self::CRON_HOOK,
                [$product_id]
            );
        }

        // Schedule sale end.
        if (
            $end_timestamp !== false &&
            $end_timestamp > time()
        ) {
            wp_schedule_single_event(
                $end_timestamp,
                self::CRON_HOOK,
                [$product_id]
            );
        }
    }

    /**
     * Refresh active product price.
     */
    public function refresh_product_price(int $product_id): void
    {
        if (get_post_type($product_id) !== 'cwc_product') {
            return;
        }

        $pricing = new ProductPricing();

        $pricing->update_active_price($product_id);
    }

    /**
     * Convert WordPress local datetime to timestamp.
     */
    private function get_timestamp(string $datetime): int|false
    {
        if ($datetime === '') {
            return false;
        }

        $date = \DateTimeImmutable::createFromFormat(
            '!Y-m-d\TH:i',
            $datetime,
            wp_timezone()
        );

        if (
            !$date ||
            $date->format('Y-m-d\TH:i') !== $datetime
        ) {
            return false;
        }

        return $date->getTimestamp();
    }
}
