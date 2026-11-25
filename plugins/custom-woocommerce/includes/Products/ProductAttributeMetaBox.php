<?php

namespace CustomWooCommerce\Products;

use CustomWooCommerce\Attributes\AttributeRepository;

defined('ABSPATH') || exit;

class ProductAttributeMetaBox
{
    private ProductAttribute $product_attribute;
    private AttributeRepository $repository;

    public function __construct()
    {
        $this->product_attribute = new ProductAttribute();
        $this->repository = new AttributeRepository();
    }

    /**
     * Register product attributes meta box.
     */
    public function register(): void
    {
        add_meta_box(
            'cwc_product_attributes',
            __('Product Attributes', 'custom-woocommerce'),
            [$this, 'render'],
            'cwc_product',
            'normal',
            'default'
        );
    }

    /**
     * Render product attributes.
     */
    public function render(\WP_Post $post): void
    {
        $attributes = $this->product_attribute->get_attributes($post->ID);
        $global_attributes = $this->repository->get_attributes();

        $attribute_data = [];

        foreach ($global_attributes as $global_attribute) {
            $attribute_id = (int) $global_attribute['attribute_id'];

            $attribute_data[$attribute_id] = [
                'label' => $global_attribute['attribute_label'],
                'terms' => $this->repository->get_terms($attribute_id),
            ];
        }

        wp_nonce_field(
            'cwc_save_product_attributes',
            'cwc_product_attributes_nonce'
        );

        $selected_attribute_ids = array_map(
            'intval',
            array_column($attributes, 'attribute_id')
        );

        ?>
        <div
            id="cwc-product-attributes"
            data-attributes="<?php echo esc_attr(wp_json_encode($attribute_data)); ?>"
        >

            <?php foreach ($attributes as $index => $attribute) : ?>

                <?php
                $attribute_id = (int) $attribute['attribute_id'];
                $terms = $this->repository->get_terms($attribute_id);

                $selected_term_ids = array_map(
                    'intval',
                    array_column($attribute['options'], 'term_id')
                );
                ?>

                <div class="cwc-attribute-row">

                    <p>
                        <label>
                            <strong>
                                <?php esc_html_e('Attribute', 'custom-woocommerce'); ?>
                            </strong>
                        </label>

                        <select
                            name="cwc_attributes[<?php echo esc_attr($index); ?>][attribute_id]"
                            class="widefat cwc-attribute-select"
                        >
                            <option value="">
                                <?php esc_html_e('Select attribute', 'custom-woocommerce'); ?>
                            </option>

                            <?php foreach ($global_attributes as $global_attribute) : ?>
                                <option
                                    value="<?php echo esc_attr($global_attribute['attribute_id']); ?>"
                                    <?php selected($attribute_id, (int) $global_attribute['attribute_id']); ?>
                                >
                                    <?php echo esc_html($global_attribute['attribute_label']); ?>
                                </option>
                            <?php endforeach; ?>

                        </select>
                    </p>

                 
<div class="cwc-multiselect">

<label>
    <strong>
        <?php esc_html_e('Options', 'custom-woocommerce'); ?>
    </strong>
</label>

<input
    type="search"
    class="widefat cwc-options-search"
    placeholder="<?php esc_attr_e('Search options...', 'custom-woocommerce'); ?>"
    autocomplete="off"
>

<div class="cwc-selected-options"></div>

<div class="cwc-options-dropdown">

    <?php foreach ($terms as $term) : ?>

        <label class="cwc-option-item">

            <input
                type="checkbox"
                name="cwc_attributes[<?php echo esc_attr($index); ?>][options][]"
                value="<?php echo esc_attr($term['term_id']); ?>"
                <?php checked(
                    in_array((int) $term['term_id'], $selected_term_ids, true)
                ); ?>
            >

            <span>
                <?php echo esc_html($term['name']); ?>
            </span>

        </label>

    <?php endforeach; ?>

</div>

</div>


                    <p>
                        <label>
                            <input
                                type="checkbox"
                                name="cwc_attributes[<?php echo esc_attr($index); ?>][visible]"
                                value="1"
                                <?php checked(!empty($attribute['visible'])); ?>
                            >
                            <?php esc_html_e('Visible on product page', 'custom-woocommerce'); ?>
                        </label>
                    </p>

                    <p>
                        <label>
                            <input
                                type="checkbox"
                                name="cwc_attributes[<?php echo esc_attr($index); ?>][variation]"
                                value="1"
                                <?php checked(!empty($attribute['variation'])); ?>
                            >
                            <?php esc_html_e('Used for variations', 'custom-woocommerce'); ?>
                        </label>
                    </p>

                    <button
                        type="button"
                        class="button cwc-remove-attribute"
                    >
                        <?php esc_html_e('Remove', 'custom-woocommerce'); ?>
                    </button>

                    <hr>

                </div>

            <?php endforeach; ?>

        </div>

        <p>
            <button
                type="button"
                id="cwc-add-attribute"
                class="button button-secondary"
            >
                <?php esc_html_e('Add Attribute', 'custom-woocommerce'); ?>
            </button>
        </p>
        <?php
    }

    /**
     * Save product attributes.
     */
    public function save(int $post_id): void
    {
        if (
            !isset($_POST['cwc_product_attributes_nonce']) ||
            !wp_verify_nonce(
                sanitize_text_field(
                    wp_unslash($_POST['cwc_product_attributes_nonce'])
                ),
                'cwc_save_product_attributes'
            )
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (get_post_type($post_id) !== 'cwc_product') {
            return;
        }

        $attributes = [];

        if (
            isset($_POST['cwc_attributes']) &&
            is_array($_POST['cwc_attributes'])
        ) {
            $submitted_attributes = wp_unslash($_POST['cwc_attributes']);

            foreach ($submitted_attributes as $attribute) {
                if (!is_array($attribute)) {
                    continue;
                }

                $attributes[] = [
                    'attribute_id' => absint($attribute['attribute_id'] ?? 0),
                    'options'      => isset($attribute['options']) && is_array($attribute['options'])
                        ? array_map('absint', $attribute['options'])
                        : [],
                    'visible'      => !empty($attribute['visible']),
                    'variation'    => !empty($attribute['variation']),
                ];
            }
        }

        $this->product_attribute->save_attributes(
            $post_id,
            $attributes
        );
    }
}
