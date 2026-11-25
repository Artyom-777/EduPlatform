<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductVariationMeta
{
    /**
     * Register variation meta fields.
     */
    public function register_meta(): void
    {
        $meta_fields = [
            '_cwc_parent_product_id' => [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
            ],
            '_cwc_variation_attributes' => [
                'type' => 'object',
                'sanitize_callback' => [$this, 'sanitize_attributes'],
            ],
            '_cwc_variation_sku' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
            '_cwc_variation_regular_price' => [
                'type' => 'string',
                'sanitize_callback' => [$this, 'sanitize_price'],
            ],
            '_cwc_variation_sale_price' => [
                'type' => 'string',
                'sanitize_callback' => [$this, 'sanitize_price'],
            ],
            '_cwc_variation_stock_quantity' => [
                'type' => 'integer',
                'sanitize_callback' => 'absint',
            ],
            '_cwc_variation_stock_status' => [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ],
        ];

        foreach ($meta_fields as $meta_key => $args) {
            register_post_meta(
                'cwc_variation',
                $meta_key,
                array_merge(
                    $args,
                    [
                        'single' => true,
                        'show_in_rest' => false,
                        'auth_callback' => function ($allowed, $meta_key, $post_id, $user_id) {
                            return user_can($user_id, 'edit_post', $post_id);
                        },
                    ]
                )
            );
        }
    }

    /**
     * Sanitize variation attributes.
     *
     * Expected format:
     * [
     *     'attribute_12' => 34,
     *     'attribute_13' => 38,
     * ]
     */
    public function sanitize_attributes($attributes): array
    {
        if (!is_array($attributes)) {
            return [];
        }

        $repository = new \CustomWooCommerce\Attributes\AttributeRepository();

        $global_attributes = $repository->get_attributes();

        $valid_attribute_ids = array_map(
            'intval',
            array_column($global_attributes, 'attribute_id')
        );

        $sanitized = [];

        foreach ($attributes as $key => $value) {
            if (!is_string($key) || !preg_match('/^attribute_(\d+)$/', $key, $matches)) {
                continue;
            }

            $attribute_id = (int) $matches[1];
            $term_id = absint($value);

            if (
                !in_array($attribute_id, $valid_attribute_ids, true) ||
                !$term_id
            ) {
                continue;
            }

            $terms = $repository->get_terms($attribute_id);

            $valid_term_ids = array_map(
                'intval',
                array_column($terms, 'term_id')
            );

            if (!in_array($term_id, $valid_term_ids, true)) {
                continue;
            }

            $sanitized[$key] = $term_id;
        }

        return $sanitized;
    }

    /**
     * Sanitize variation price.
     */
    public function sanitize_price($price): string
    {
        $price = trim((string) $price);

        if ($price === '' || !is_numeric($price)) {
            return '';
        }

        return number_format((float) $price, 2, '.', '');
    }
}
