<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductAdminColumns
{
    /**
     * Register custom product columns.
     */
    public function register_columns(array $columns): array
    {
        $new_columns = [];

        foreach ($columns as $key => $label) {
            $new_columns[$key] = $label;

            if ($key === 'title') {
                $new_columns['cwc_sku'] = __('SKU', 'custom-woocommerce');
                $new_columns['cwc_price'] = __('Price', 'custom-woocommerce');
                $new_columns['cwc_stock'] = __('Stock', 'custom-woocommerce');
            }
        }

        return $new_columns;
    }

    /**
     * Render custom column values.
     */
    public function render_column(string $column, int $post_id): void
    {
        switch ($column) {
            case 'cwc_sku':
                echo esc_html(
                    get_post_meta($post_id, '_cwc_sku', true) ?: '—'
                );
                break;

                case 'cwc_price':
                    $regular_price = get_post_meta(
                        $post_id,
                        '_cwc_regular_price',
                        true
                    );
                
                    $pricing = new ProductPricing();
                
                    $active_price = $pricing->get_active_price($post_id);
                
                    if (
                        $active_price !== '' &&
                        $regular_price !== '' &&
                        (float) $active_price < (float) $regular_price
                    ) {
                        echo '<del>' . esc_html($regular_price) . '</del> ';
                        echo '<ins>' . esc_html($active_price) . '</ins>';
                    } else {
                        echo esc_html($regular_price !== '' ? $regular_price : '—');
                    }
                
                    break;

              
                case 'cwc_stock':
                    $manage_stock = get_post_meta(
                        $post_id,
                        '_cwc_manage_stock',
                        true
                    );

                    $quantity = get_post_meta(
                        $post_id,
                        '_cwc_stock_quantity',
                        true
                    );

                    $status = get_post_meta(
                        $post_id,
                        '_cwc_stock_status',
                        true
                    );

                    if ($manage_stock === 'yes') {
                        if ((int) $quantity > 0) {
                            echo esc_html($quantity . ' in stock');
                        } else {
                            echo esc_html__('Out of stock', 'custom-woocommerce');
                        }
                    } else {
                        $statuses = [
                            'instock'     => __('In stock', 'custom-woocommerce'),
                            'outofstock'  => __('Out of stock', 'custom-woocommerce'),
                            'onbackorder' => __('On backorder', 'custom-woocommerce'),
                        ];

                        echo esc_html($statuses[$status] ?? '—');
                    }

                    break;

            }
    }
}
