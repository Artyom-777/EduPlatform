<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

use CustomWooCommerce\Attributes\AttributeRepository;

class ProductVariationMetaBox
{
    private ProductVariation $variation;
    private ProductAttribute $product_attribute;
    private AttributeRepository $attribute_repository;

    public function __construct()
    {
        $this->variation = new ProductVariation();
        $this->product_attribute = new ProductAttribute();
        $this->attribute_repository = new AttributeRepository();
    }

    /**
     * Register variation meta box.
     */
    public function register(): void
    {
        add_meta_box(
            'cwc_product_variations',
            __('Product Variations', 'custom-woocommerce'),
            [$this, 'render'],
            'cwc_product',
            'normal',
            'default'
        );
    }

    /**
     * Render variation meta box.
     */
    public function render(\WP_Post $post): void
    {
        $product_type = get_post_meta(
            $post->ID,
            '_cwc_product_type',
            true
        );

        if ($product_type !== 'variable') {
            echo '<p>';
            esc_html_e(
                'Select Variable product to manage variations.',
                'custom-woocommerce'
            );
            echo '</p>';

            return;
        }

        $product_attributes = $this->product_attribute->get_attributes(
            $post->ID
        );

        $variation_attributes = array_filter(
            $product_attributes,
            static function ($attribute) {
                return !empty($attribute['variation']);
            }
        );

        $variations = $this->variation->get_product_variations(
            $post->ID
        );

        echo '<div id="cwc-product-variations">';

        echo '<h3>';
        esc_html_e('Existing Variations', 'custom-woocommerce');
        echo '</h3>';

        if (empty($variations)) {
            echo '<p>';
            esc_html_e(
                'No variations have been created yet.',
                'custom-woocommerce'
            );
            echo '</p>';
        } else {
            echo '<table class="widefat striped">';
            echo '<thead><tr>';

            echo '<th>' . esc_html__('Attributes', 'custom-woocommerce') . '</th>';
            echo '<th>' . esc_html__('SKU', 'custom-woocommerce') . '</th>';
            echo '<th>' . esc_html__('Regular Price', 'custom-woocommerce') . '</th>';
            echo '<th>' . esc_html__('Sale Price', 'custom-woocommerce') . '</th>';
            echo '<th>' . esc_html__('Stock', 'custom-woocommerce') . '</th>';

            echo '</tr></thead><tbody>';

            foreach ($variations as $variation) {
                echo '<tr>';

                echo '<td>';

                foreach ($variation['attributes'] as $attribute_key => $term_id) {
                    $attribute_id = (int) str_replace(
                        'attribute_',
                        '',
                        $attribute_key
                    );

                    $attribute = $this->find_attribute(
                        $product_attributes,
                        $attribute_id
                    );

                    $term = $this->find_term(
                        $attribute['options'] ?? [],
                        (int) $term_id
                    );

                    if ($attribute && $term) {
                        echo '<strong>' . esc_html($attribute['name']) . ':</strong> ';
                        echo esc_html($term['name']) . '<br>';
                    }
                }

                echo '</td>';

                echo '<td>' . esc_html($variation['sku']) . '</td>';
                echo '<td>' . esc_html($variation['regular_price']) . '</td>';
                echo '<td>' . esc_html($variation['sale_price']) . '</td>';

                echo '<td>';
                echo esc_html($variation['stock_quantity']);
                echo ' / ';
                echo esc_html($variation['stock_status']);
                echo '</td>';

                echo '</tr>';
            }

            echo '</tbody></table>';
        }

        echo '<hr>';

        echo '<h3>';
        esc_html_e('Add New Variation', 'custom-woocommerce');
        echo '</h3>';

        if (empty($variation_attributes)) {
            echo '<p>';
            esc_html_e(
                'First add product attributes and enable Used for variations.',
                'custom-woocommerce'
            );
            echo '</p>';

            echo '</div>';

            return;
        }

        ?>
        <div
            id="cwc-variation-create-form"
            data-action-url="<?php echo esc_url(admin_url('admin-post.php')); ?>"
        >

            <input type="hidden" name="action" value="cwc_create_variation">

            <input type="hidden" name="product_id" value="<?php echo esc_attr($post->ID); ?>">

            <?php wp_nonce_field('cwc_create_variation_' . $post->ID); ?>

            <table class="form-table">
                <tbody>

                    <?php foreach ($variation_attributes as $attribute) : ?>

                        <tr>
                            <th>
                                <label>
                                    <?php echo esc_html($attribute['name']); ?>
                                </label>
                            </th>

                            <td>
                                <select
                                    name="attributes[attribute_<?php echo esc_attr($attribute['attribute_id']); ?>]"
                                >
                                    <option value="">
                                        <?php esc_html_e('Choose an option', 'custom-woocommerce'); ?>
                                    </option>

                                    <?php foreach ($attribute['options'] as $term) : ?>

                                        <option value="<?php echo esc_attr($term['term_id']); ?>">
                                            <?php echo esc_html($term['name']); ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    <tr>
                        <th>
                            <label for="cwc-variation-sku">
                                <?php esc_html_e('SKU', 'custom-woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <input
                                type="text"
                                id="cwc-variation-sku"
                                name="sku"
                                class="regular-text"
                            >
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="cwc-variation-regular-price">
                                <?php esc_html_e('Regular Price', 'custom-woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <input
                                type="number"
                                id="cwc-variation-regular-price"
                                name="regular_price"
                                min="0"
                                step="0.01"
                                class="regular-text"
                            >
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="cwc-variation-sale-price">
                                <?php esc_html_e('Sale Price', 'custom-woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <input
                                type="number"
                                id="cwc-variation-sale-price"
                                name="sale_price"
                                min="0"
                                step="0.01"
                                class="regular-text"
                            >
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="cwc-variation-stock-quantity">
                                <?php esc_html_e('Stock Quantity', 'custom-woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <input
                                type="number"
                                id="cwc-variation-stock-quantity"
                                name="stock_quantity"
                                min="0"
                                step="1"
                                value="0"
                                class="regular-text"
                            >
                        </td>
                    </tr>

                    <tr>
                        <th>
                            <label for="cwc-variation-stock-status">
                                <?php esc_html_e('Stock Status', 'custom-woocommerce'); ?>
                            </label>
                        </th>
                        <td>
                            <select
                                id="cwc-variation-stock-status"
                                name="stock_status"
                            >
                                <option value="instock">
                                    <?php esc_html_e('In stock', 'custom-woocommerce'); ?>
                                </option>
                                <option value="outofstock">
                                    <?php esc_html_e('Out of stock', 'custom-woocommerce'); ?>
                                </option>
                                <option value="onbackorder">
                                    <?php esc_html_e('On backorder', 'custom-woocommerce'); ?>
                                </option>
                            </select>
                        </td>
                    </tr>

                </tbody>
            </table>

            <button
                type="button"
                id="cwc-create-variation"
                class="button button-primary"
            >
                <?php esc_html_e('Create Variation', 'custom-woocommerce'); ?>
            </button>

        </div>
        <?php

        echo '</div>';
    }


/**
 * Handle variation creation request.
 */
public function create_variation(): void
{
    $product_id = isset($_POST['product_id'])
        ? absint($_POST['product_id'])
        : 0;

    if (!$product_id) {
        wp_die('Invalid product ID.');
    }

    // Verify user permission.
    if (!current_user_can('edit_post', $product_id)) {
        wp_die(
            esc_html__('You are not allowed to create variations.', 'custom-woocommerce')
        );
    }

    // Verify nonce.
    check_admin_referer('cwc_create_variation_' . $product_id);

    // Verify product type.
    if (
        get_post_type($product_id) !== 'cwc_product' ||
        get_post_meta($product_id, '_cwc_product_type', true) !== 'variable'
    ) {
        $this->redirect_with_notice($product_id, 'invalid_product');
    }

    // Read submitted attributes.
    $attributes = isset($_POST['attributes']) && is_array($_POST['attributes'])
        ? wp_unslash($_POST['attributes'])
        : [];

    // Create variation.
    $variation_id = $this->variation->create_variation(
        $product_id,
        $attributes
    );

    if (!$variation_id) {
        $this->redirect_with_notice($product_id, 'creation_failed');
    }

    // Prepare variation data.
    $variation_data = [
        'sku' => isset($_POST['sku'])
            ? sanitize_text_field(wp_unslash($_POST['sku']))
            : '',

        'regular_price' => isset($_POST['regular_price'])
            ? sanitize_text_field(wp_unslash($_POST['regular_price']))
            : '',

        'sale_price' => isset($_POST['sale_price'])
            ? sanitize_text_field(wp_unslash($_POST['sale_price']))
            : '',

        'stock_quantity' => isset($_POST['stock_quantity'])
            ? absint($_POST['stock_quantity'])
            : 0,

        'stock_status' => isset($_POST['stock_status'])
            ? sanitize_key(wp_unslash($_POST['stock_status']))
            : 'instock',
    ];

        // Update variation data.
        $updated = $this->variation->update_variation(
            $variation_id,
            $variation_data
        );

        if (!$updated) {
            $this->variation->delete_variation($variation_id);

            $this->redirect_with_notice($product_id, 'validation_failed');
        }

        $this->redirect_with_notice($product_id, 'created');
    }

    /**
     * Redirect to product editor with a notice.
     */
    private function redirect_with_notice(
        int $product_id,
        string $notice
    ): void {
        $url = get_edit_post_link($product_id, 'raw');

        if (!$url) {
            wp_die('Unable to redirect to product editor.');
        }

        wp_safe_redirect(
            add_query_arg(
                'cwc_variation_notice',
                $notice,
                $url
            )
        );

        exit;
    }

    /**
     * Render variation admin notices.
     */
    public function render_variation_notice(): void
    {
        if (!isset($_GET['cwc_variation_notice'])) {
            return;
        }

        $notice = sanitize_key(
            wp_unslash($_GET['cwc_variation_notice'])
        );

        $messages = [
            'created' => [
                'success',
                __('Variation created successfully.', 'custom-woocommerce'),
            ],
            'creation_failed' => [
                'error',
                __('Variation could not be created. Check its attributes.', 'custom-woocommerce'),
            ],
            'validation_failed' => [
                'error',
                __('Variation data is invalid. Check SKU and prices.', 'custom-woocommerce'),
            ],
            'invalid_product' => [
                'error',
                __('Invalid variable product.', 'custom-woocommerce'),
            ],
        ];

        if (!isset($messages[$notice])) {
            return;
        }

        [$type, $message] = $messages[$notice];

        printf(
            '<div class="notice notice-%1$s is-dismissible"><p>%2$s</p></div>',
            esc_attr($type),
            esc_html($message)
        );
    }


    /**
     * Find a product attribute by ID.
     */
    private function find_attribute(array $attributes, int $attribute_id): ?array
    {
        foreach ($attributes as $attribute) {
            if ((int) $attribute['attribute_id'] === $attribute_id) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * Find a term by ID.
     */
    private function find_term(array $terms, int $term_id): ?array
    {
        foreach ($terms as $term) {
            if ((int) $term['term_id'] === $term_id) {
                return $term;
            }
        }

        return null;
    }
}
