<?php

namespace CustomWooCommerce\Products;

use CustomWooCommerce\Products\ProductType;

defined('ABSPATH') || exit;

class ProductMetaBox
{
    public function register(): void
    {
        add_meta_box(
            'cwc_product_data',
            __('Product Data', 'custom-woocommerce'),
            [$this, 'render'],
            'cwc_product',
            'normal',
            'high'
        );
    }

    public function render(\WP_Post $post): void
    {
        $sku = get_post_meta($post->ID, '_cwc_sku', true);

        $regular_price = get_post_meta(
            $post->ID,
            '_cwc_regular_price',
            true
        );

        $sale_price = get_post_meta(
            $post->ID,
            '_cwc_sale_price',
            true
        );

        $manage_stock = get_post_meta(
            $post->ID,
            '_cwc_manage_stock',
            true
        );

        $stock_quantity = get_post_meta(
            $post->ID,
            '_cwc_stock_quantity',
            true
        );

        $stock_status = get_post_meta(
            $post->ID,
            '_cwc_stock_status',
            true
        );

        $sale_start = get_post_meta(
            $post->ID,
            '_cwc_sale_start',
            true
        );
        
        $sale_end = get_post_meta(
            $post->ID,
            '_cwc_sale_end',
            true
        );

        wp_nonce_field(
            'cwc_save_product_data',
            'cwc_product_nonce'
        );

        // Get the current product type.
        $product_type = get_post_meta(
            $post->ID,
            '_cwc_product_type',
            true
        );

        if (empty($product_type)) {
            $product_type = 'simple';
        }

        // Get available product types.
        $product_types = ProductType::get_types();
        ?>

        <p>
            <label for="cwc_product_type">
                <strong>
                    <?php esc_html_e('Product Type', 'custom-woocommerce'); ?>
                </strong>
            </label>
        </p>

        <select
            name="cwc_product_type"
            id="cwc_product_type"
            class="widefat"
        >
            <?php foreach ($product_types as $value => $label) : ?>
                <option
                    value="<?php echo esc_attr($value); ?>"
                    <?php selected($product_type, $value); ?>
                >
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        

        <div class="cwc-product-data" id="cwc-simple-product-fields">

            <p>
                <label for="cwc_sku">
                    <strong><?php esc_html_e('SKU', 'custom-woocommerce'); ?></strong>
                </label>

                <input
                    type="text"
                    id="cwc_sku"
                    name="cwc_sku"
                    value="<?php echo esc_attr($sku); ?>"
                    class="widefat"
                >
            </p>

            <p>
                <label for="cwc_regular_price">
                    <strong><?php esc_html_e('Regular Price', 'custom-woocommerce'); ?></strong>
                </label>

                <input
                    type="number"
                    id="cwc_regular_price"
                    name="cwc_regular_price"
                    value="<?php echo esc_attr($regular_price); ?>"
                    min="0"
                    step="0.01"
                    class="widefat"
                >
            </p>

            <p>
                <label for="cwc_sale_price">
                    <strong><?php esc_html_e('Sale Price', 'custom-woocommerce'); ?></strong>
                </label>

                <input
                    type="number"
                    id="cwc_sale_price"
                    name="cwc_sale_price"
                    value="<?php echo esc_attr($sale_price); ?>"
                    min="0"
                    step="0.01"
                    class="widefat"
                >
            </p>


            <p>
                <label for="cwc_sale_start">
                    <strong>
                        <?php esc_html_e('Sale Start Date', 'custom-woocommerce'); ?>
                    </strong>
                </label>

                <input
                    type="datetime-local"
                    id="cwc_sale_start"
                    name="cwc_sale_start"
                    value="<?php echo esc_attr($sale_start); ?>"
                    class="widefat"
                >
            </p>

            <p>
                <label for="cwc_sale_end">
                    <strong>
                        <?php esc_html_e('Sale End Date', 'custom-woocommerce'); ?>
                    </strong>
                </label>

                <input
                    type="datetime-local"
                    id="cwc_sale_end"
                    name="cwc_sale_end"
                    value="<?php echo esc_attr($sale_end); ?>"
                    class="widefat"
                >
            </p>


            <p>
                <label>
                    <input
                        type="checkbox"
                        name="cwc_manage_stock"
                        value="yes"
                        <?php checked($manage_stock, 'yes'); ?>
                    >
                    <?php esc_html_e('Manage stock', 'custom-woocommerce'); ?>
                </label>
            </p>

            <p>
                <label for="cwc_stock_quantity">
                    <strong><?php esc_html_e('Stock Quantity', 'custom-woocommerce'); ?></strong>
                </label>

                <input
                    type="number"
                    id="cwc_stock_quantity"
                    name="cwc_stock_quantity"
                    value="<?php echo esc_attr($stock_quantity); ?>"
                    min="0"
                    step="1"
                    class="widefat"
                >
            </p>

            <p>
                <label for="cwc_stock_status">
                    <strong><?php esc_html_e('Stock Status', 'custom-woocommerce'); ?></strong>
                </label>

                <select
                    id="cwc_stock_status"
                    name="cwc_stock_status"
                    class="widefat"
                >
                    <option value="instock" <?php selected($stock_status, 'instock'); ?>>
                        <?php esc_html_e('In stock', 'custom-woocommerce'); ?>
                    </option>

                    <option value="outofstock" <?php selected($stock_status, 'outofstock'); ?>>
                        <?php esc_html_e('Out of stock', 'custom-woocommerce'); ?>
                    </option>

                    <option value="onbackorder" <?php selected($stock_status, 'onbackorder'); ?>>
                        <?php esc_html_e('On backorder', 'custom-woocommerce'); ?>
                    </option>
                </select>
            </p>

        </div>

        <?php
    }


    public function save(int $post_id): void
    {
        // Verify nonce.
        if (
            !isset($_POST['cwc_product_nonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(wp_unslash($_POST['cwc_product_nonce'])),
                'cwc_save_product_data'
            )
        ) {
            return;
        }

        // Check autosave.
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check post type.
        if (get_post_type($post_id) !== 'cwc_product') {
            return;
        }

        // Check user permissions.
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $product_type = isset($_POST['cwc_product_type'])
            ? sanitize_text_field(wp_unslash($_POST['cwc_product_type']))
            : 'simple';

        if (!ProductType::is_valid($product_type)) {
            $product_type = 'simple';
        }

        update_post_meta(
            $post_id,
            '_cwc_product_type',
            $product_type
        );
        
        if ($product_type === 'simple') {
        
            // Validate and save SKU.
            $sku = isset($_POST['cwc_sku'])
                ? sanitize_text_field(wp_unslash($_POST['cwc_sku']))
                : '';
        
            $validator = new ProductValidator();
        
            if (!$validator->validate_sku($sku, $post_id)) {
                set_transient(
                    'cwc_sku_error_' . get_current_user_id(),
                    __('This SKU is already assigned to another product.', 'custom-woocommerce'),
                    60
                );
        
                return;
            }
        
            update_post_meta(
                $post_id,
                '_cwc_sku',
                $sku
            );
        
            // Save regular price.
            if (isset($_POST['cwc_regular_price'])) {
                update_post_meta(
                    $post_id,
                    '_cwc_regular_price',
                    $this->sanitize_price($_POST['cwc_regular_price'])
                );
            }
        
            // Save sale price.
            if (isset($_POST['cwc_sale_price'])) {
                update_post_meta(
                    $post_id,
                    '_cwc_sale_price',
                    $this->sanitize_price($_POST['cwc_sale_price'])
                );
            }
        
            // Save sale start date.
            if (isset($_POST['cwc_sale_start'])) {
                update_post_meta(
                    $post_id,
                    '_cwc_sale_start',
                    sanitize_text_field(
                        wp_unslash($_POST['cwc_sale_start'])
                    )
                );
            }
        
            // Save sale end date.
            if (isset($_POST['cwc_sale_end'])) {
                update_post_meta(
                    $post_id,
                    '_cwc_sale_end',
                    sanitize_text_field(
                        wp_unslash($_POST['cwc_sale_end'])
                    )
                );
            }
        
            // Update active product price.
            $pricing = new ProductPricing();
            $pricing->update_active_price($post_id);
        
            // Schedule sale events.
            $scheduler = new ProductSaleScheduler();
            $scheduler->schedule($post_id);
        
            // Save stock management.
            update_post_meta(
                $post_id,
                '_cwc_manage_stock',
                isset($_POST['cwc_manage_stock']) ? 'yes' : 'no'
            );
        
            // Save stock quantity.
            if (isset($_POST['cwc_stock_quantity'])) {
                update_post_meta(
                    $post_id,
                    '_cwc_stock_quantity',
                    absint(wp_unslash($_POST['cwc_stock_quantity']))
                );
            }
        
            // Save stock status.
            if (isset($_POST['cwc_stock_status'])) {
                $allowed_statuses = [
                    'instock',
                    'outofstock',
                    'onbackorder',
                ];
        
                $stock_status = sanitize_text_field(
                    wp_unslash($_POST['cwc_stock_status'])
                );
        
                if (in_array($stock_status, $allowed_statuses, true)) {
                    update_post_meta(
                        $post_id,
                        '_cwc_stock_status',
                        $stock_status
                    );
                }
            }    
        }
    }

   
    public function render_sku_error_notice(): void
    {
        if (
            !isset($_GET['cwc_sku_error']) ||
            sanitize_text_field(
                wp_unslash($_GET['cwc_sku_error'])
            ) !== '1'
        ) {
            return;
        }
    
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <?php
                esc_html_e(
                    'This SKU is already assigned to another product.',
                    'custom-woocommerce'
                );
                ?>
            </p>
        </div>
        <?php
    }


    private function sanitize_price($price): string
    {
        $price = is_scalar($price) ? trim((string) $price) : '';

        if ($price === '' || !preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
            return '';
        }
        
        return number_format((float) $price, 2, '.', '');   
    }

    public function add_sku_error_to_redirect(
        string $location,
        int $post_id
    ): string {
        $transient_key = 'cwc_sku_error_' . get_current_user_id();
    
        if (get_transient($transient_key)) {
            delete_transient($transient_key);
    
            $location = add_query_arg(
                'cwc_sku_error',
                '1',
                $location
            );
        }
    
        return $location;
    }

}
