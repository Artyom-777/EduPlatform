<?php

namespace CustomWooCommerce\Products;

use CustomWooCommerce\Products\ProductType;

defined('ABSPATH') || exit;

class ProductMeta
{
    public function register(): void
    {
        $this->register_meta('_cwc_sku', 'string');
        $this->register_meta('_cwc_regular_price', 'string');
        $this->register_meta('_cwc_sale_price', 'string');
        $this->register_meta('_cwc_price', 'string');

        $this->register_meta('_cwc_manage_stock', 'string');
        $this->register_meta('_cwc_stock_quantity', 'integer');
        $this->register_meta('_cwc_stock_status', 'string');

        $this->register_meta('_cwc_virtual', 'boolean');
        $this->register_meta('_cwc_downloadable', 'boolean');

        register_post_meta(
            'cwc_product',
            '_cwc_product_type',
            [
                'type'              => 'string',
                'single'            => true,
                'show_in_rest'      => true,
                'default'           => 'simple',
                'sanitize_callback' => function ($value) {
                    $value = sanitize_text_field($value);
        
                    return ProductType::is_valid($value)
                        ? $value
                        : 'simple';
                },
                'auth_callback'     => function ($allowed, $meta_key, $post_id, $user_id) {
                    return user_can($user_id, 'edit_post', $post_id);
                },
            ]
        );
    }

    private function register_meta(string $key, string $type): void
    {
        register_post_meta(
            'cwc_product',
            $key,
            [
                'type'              => $type,
                'single'            => true,
                'show_in_rest'      => true,
                'sanitize_callback' => $this->get_sanitize_callback($type),
                'auth_callback'     => [$this, 'authorize_meta'],
            ]
        );
    }

    private function get_sanitize_callback(string $type): callable
    {
        return match ($type) {
            'boolean' => 'rest_sanitize_boolean',
            'integer' => 'absint',
            default   => 'sanitize_text_field',
        };
    }

    public function authorize_meta(
        bool $allowed,
        string $meta_key,
        int $post_id,
        int $user_id
    ): bool {
        return user_can($user_id, 'edit_post', $post_id);
    }
}
