<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductVariation
{
    /**
     * Register product variation post type.
     */
    public function register_post_type(): void
    {
        register_post_type(
            'cwc_variation',
            [
                'labels' => [
                    'name' => __('Product Variations', 'custom-woocommerce'),
                    'singular_name' => __('Product Variation', 'custom-woocommerce'),
                ],

                'public'       => false,
                'show_ui'      => false,
                'show_in_menu' => false,
                'show_in_rest' => false,

                'supports' => [
                    'title',
                    'custom-fields',
                ],

                'hierarchical' => false,
            ]
        );
    }

    /**
     * Create a new product variation.
     */
    public function create_variation(
        int $parent_product_id,
        array $attributes = []
    ): int {
        // Verify parent product.
        if (
            get_post_type($parent_product_id) !== 'cwc_product' ||
            get_post_meta($parent_product_id, '_cwc_product_type', true) !== 'variable'
        ) {
            return 0;
        }

        // Sanitize variation attributes.
        $variation_meta = new ProductVariationMeta();

        $attributes = $variation_meta->sanitize_attributes($attributes);
        
        $attributes = $this->validate_variation_attributes(
            $parent_product_id,
            $attributes
        );
        
        if (empty($attributes)) {
            return 0;
        }

        // Prevent duplicate variations.
        if (
            $this->variation_exists(
                $parent_product_id,
                $attributes
            )
        ) {
            return 0;
        }

        // Create variation post.
        $variation_id = wp_insert_post([
            'post_type'   => 'cwc_variation',
            'post_status' => 'publish',
            'post_title'  => 'Variation of product #' . $parent_product_id,
        ], true);

        if (is_wp_error($variation_id)) {
            return 0;
        }

        // Connect variation to parent product.
        update_post_meta(
            $variation_id,
            '_cwc_parent_product_id',
            $parent_product_id
        );

        // Save variation attributes.
        update_post_meta(
            $variation_id,
            '_cwc_variation_attributes',
            $attributes
        );

        return (int) $variation_id;
    }

    /**
     * Update variation data.
     */
    public function update_variation(
        int $variation_id,
        array $data
    ): bool {
    // Verify variation post type.
    if (get_post_type($variation_id) !== 'cwc_variation') {
        return false;
    }

    $variation_meta = new ProductVariationMeta();

    // Update SKU.
    if (isset($data['sku'])) {
        $sku = sanitize_text_field($data['sku']);
    
        if (!$this->is_sku_unique($sku, $variation_id)) {
            return false;
        }
    
        update_post_meta(
            $variation_id,
            '_cwc_variation_sku',
            $sku
        );
    }

    // Update regular price and sale price.
    if (
        isset($data['regular_price']) ||
        isset($data['sale_price'])
    ) {
        $variation_meta = new ProductVariationMeta();
    
        $regular_price = isset($data['regular_price'])
            ? $variation_meta->sanitize_price($data['regular_price'])
            : (string) get_post_meta(
                $variation_id,
                '_cwc_variation_regular_price',
                true
            );
    
        $sale_price = isset($data['sale_price'])
            ? $variation_meta->sanitize_price($data['sale_price'])
            : (string) get_post_meta(
                $variation_id,
                '_cwc_variation_sale_price',
                true
            );
    
        if ($sale_price !== '') {
            if (
                $regular_price === '' ||
                (float) $sale_price >= (float) $regular_price
            ) {
                return false;
            }
        }
    
        if (isset($data['regular_price'])) {
            update_post_meta(
                $variation_id,
                '_cwc_variation_regular_price',
                $regular_price
            );
        }
    
        if (isset($data['sale_price'])) {
            update_post_meta(
                $variation_id,
                '_cwc_variation_sale_price',
                $sale_price
            );
        }
    }
    
    // Update stock quantity.
    if (isset($data['stock_quantity'])) {
        update_post_meta(
            $variation_id,
            '_cwc_variation_stock_quantity',
            absint($data['stock_quantity'])
        );
    }

        // Update stock status.
        if (isset($data['stock_status'])) {
            $allowed_statuses = [
                'instock',
                'outofstock',
                'onbackorder',
            ];

            $stock_status = sanitize_text_field($data['stock_status']);

            if (!in_array($stock_status, $allowed_statuses, true)) {
                return false;
            }

            update_post_meta(
                $variation_id,
                '_cwc_variation_stock_status',
                $stock_status
            );
        }

        // Update variation attributes.
        if (isset($data['attributes'])) {
            $parent_product_id = (int) get_post_meta(
                $variation_id,
                '_cwc_parent_product_id',
                true
            );
        
            $variation_meta = new ProductVariationMeta();
        
            $attributes = $variation_meta->sanitize_attributes(
                $data['attributes']
            );
        
            $attributes = $this->validate_variation_attributes(
                $parent_product_id,
                $attributes
            );
        
            if (empty($attributes)) {
                return false;
            }
        
            update_post_meta(
                $variation_id,
                '_cwc_variation_attributes',
                $attributes
            );
        }

        return true;
    }

    /**
     * Get variation data.
     */
    public function get_variation(int $variation_id): ?array
    {
        if (get_post_type($variation_id) !== 'cwc_variation') {
            return null;
        }

        return [
            'id' => $variation_id,

            'parent_product_id' => (int) get_post_meta(
                $variation_id,
                '_cwc_parent_product_id',
                true
            ),

            'attributes' => get_post_meta(
                $variation_id,
                '_cwc_variation_attributes',
                true
            ),

            'sku' => get_post_meta(
                $variation_id,
                '_cwc_variation_sku',
                true
            ),

            'regular_price' => get_post_meta(
                $variation_id,
                '_cwc_variation_regular_price',
                true
            ),

            'sale_price' => get_post_meta(
                $variation_id,
                '_cwc_variation_sale_price',
                true
            ),

            'stock_quantity' => (int) get_post_meta(
                $variation_id,
                '_cwc_variation_stock_quantity',
                true
            ),

            'stock_status' => get_post_meta(
                $variation_id,
                '_cwc_variation_stock_status',
                true
            ),
        ];
    }

    /**
     * Get all variations of a parent product.
     */
    public function get_product_variations(int $parent_product_id): array
    {
        if (get_post_type($parent_product_id) !== 'cwc_product') {
            return [];
        }

        $variation_posts = get_posts([
            'post_type'      => 'cwc_variation',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'meta_query'     => [
                [
                    'key'     => '_cwc_parent_product_id',
                    'value'   => $parent_product_id,
                    'compare' => '=',
                    'type'    => 'NUMERIC',
                ],
            ],
        ]);

        $variations = [];

        foreach ($variation_posts as $variation_post) {
            $variation = $this->get_variation($variation_post->ID);

            if ($variation !== null) {
                $variations[] = $variation;
            }
        }

        return $variations;
    }

    /**
     * Delete a product variation permanently.
     */
    public function delete_variation(int $variation_id): bool
    {
        // Verify variation post type.
        if (get_post_type($variation_id) !== 'cwc_variation') {
            return false;
        }

        // Delete variation and its metadata permanently.
        $deleted = wp_delete_post($variation_id, true);

        return $deleted !== false && $deleted !== null;
    }

    /**
     * Validate variation attributes against the parent product.
     */
    private function validate_variation_attributes(
        int $parent_product_id,
        array $attributes
    ): array {
    $product_attribute = new ProductAttribute();

    $product_attributes = $product_attribute->get_attributes(
        $parent_product_id
    );

    $allowed_attributes = [];

    foreach ($product_attributes as $product_attribute_data) {
        if (empty($product_attribute_data['variation'])) {
            continue;
        }

        $attribute_id = (int) $product_attribute_data['attribute_id'];

        $allowed_attributes['attribute_' . $attribute_id] = array_map(
            'intval',
            array_column(
                $product_attribute_data['options'],
                'term_id'
            )
        );
    }

    // Every variation-enabled attribute must have a selected term.
    if (count($attributes) !== count($allowed_attributes)) {
        return [];
    }

    $validated = [];

    foreach ($allowed_attributes as $attribute_key => $allowed_term_ids) {
        if (!isset($attributes[$attribute_key])) {
            return [];
        }

        $term_id = absint($attributes[$attribute_key]);

        if (!in_array($term_id, $allowed_term_ids, true)) {
            return [];
        }

        $validated[$attribute_key] = $term_id;
    }

        return $validated;
    }

    /**
     * Check whether a SKU is unique across products and variations.
     */
    private function is_sku_unique(
        string $sku,
        int $exclude_variation_id = 0
    ): bool {
        $sku = trim($sku);

        if ($sku === '') {
            return true;
        }

        $args = [
            'post_type'      => ['cwc_product', 'cwc_variation'],
            'post_status'    => 'any',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'no_found_rows'  => true,
            'post__not_in'   => $exclude_variation_id
                ? [$exclude_variation_id]
                : [],
            'meta_query'     => [
                'relation' => 'OR',
                [
                    'key'     => '_cwc_sku',
                    'value'   => $sku,
                    'compare' => '=',
                ],
                [
                    'key'     => '_cwc_variation_sku',
                    'value'   => $sku,
                    'compare' => '=',
                ],
            ],
        ];

        return empty(get_posts($args));
    }

    /**
     * Check whether a variation with the same attributes already exists.
     */
    private function variation_exists(
        int $parent_product_id,
        array $attributes
    ): bool {

        $existing_variations = $this->get_product_variations(
            $parent_product_id
        );

        // Normalize attribute order.
        ksort($attributes);

        foreach ($existing_variations as $variation) {

            $existing_attributes = $variation['attributes'] ?? [];

            if (!is_array($existing_attributes)) {
                continue;
            }

            ksort($existing_attributes);

            if ($existing_attributes === $attributes) {
                return true;
            }
        }

        return false;
    }

}