<?php

namespace CustomWooCommerce\Admin;

use CustomWooCommerce\Attributes\AttributeRepository;

defined('ABSPATH') || exit;

class AttributeAdminPage
{
    private AttributeRepository $repository;

    public function __construct()
    {
        $this->repository = new AttributeRepository();
    }

    /**
     * Register the Attributes submenu page.
     */
    public function register_menu(): void
    {
        add_submenu_page(
            'edit.php?post_type=cwc_product',
            __('Attributes', 'custom-woocommerce'),
            __('Attributes', 'custom-woocommerce'),
            'manage_options',
            'cwc-attributes',
            [$this, 'render_page']
        );
    }

    /**
     * Handle attribute creation.
     */
    public function create_attribute(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to do this.', 'custom-woocommerce'));
        }

        check_admin_referer('cwc_create_attribute');

        $label = isset($_POST['attribute_label'])
            ? sanitize_text_field(wp_unslash($_POST['attribute_label']))
            : '';

        $result = $this->repository->create_attribute($label);

        $status = $result
            ? 'created'
            : 'error';

        wp_safe_redirect(
            add_query_arg(
                [
                    'post_type' => 'cwc_product',
                    'page'      => 'cwc-attributes',
                    'status'    => $status,
                ],
                admin_url('edit.php')
            )
        );

        exit;
    }


    /**
     * Handle attribute option creation.
     */
    public function create_term(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to do this.', 'custom-woocommerce'));
        }

        check_admin_referer('cwc_create_attribute_term');

        $attribute_id = isset($_POST['attribute_id'])
            ? absint($_POST['attribute_id'])
            : 0;

        $name = isset($_POST['term_name'])
            ? sanitize_text_field(wp_unslash($_POST['term_name']))
            : '';

        $result = $this->repository->add_term($attribute_id, $name);

        $status = $result ? 'term_created' : 'term_error';

        wp_safe_redirect(
            add_query_arg(
                [
                    'post_type'    => 'cwc_product',
                    'page'         => 'cwc-attributes',
                    'status'       => $status,
                    'attribute_id' => $attribute_id,
                ],
                admin_url('edit.php')
            )
        );

        exit;
    }


    /**
     * Render the Attributes admin page.
     */
    public function render_page(): void
    {
        $attributes = $this->repository->get_attributes();
        ?>
        <div class="wrap">

            <h1>
                <?php esc_html_e('Product Attributes', 'custom-woocommerce'); ?>
            </h1>

            <?php if (isset($_GET['status']) && $_GET['status'] === 'created') : ?>
                <div class="notice notice-success is-dismissible">
                    <p>
                        <?php esc_html_e('Attribute created successfully.', 'custom-woocommerce'); ?>
                    </p>
                </div>
            <?php elseif (isset($_GET['status']) && $_GET['status'] === 'error') : ?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <?php esc_html_e('Could not create attribute. It may already exist.', 'custom-woocommerce'); ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="card" style="max-width: 600px; padding: 20px;">

                <h2>
                    <?php esc_html_e('Add new attribute', 'custom-woocommerce'); ?>
                </h2>

                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

                    <input type="hidden" name="action" value="cwc_create_attribute">

                    <?php wp_nonce_field('cwc_create_attribute'); ?>

                    <p>
                        <label for="attribute_label">
                            <strong>
                                <?php esc_html_e('Name', 'custom-woocommerce'); ?>
                            </strong>
                        </label>
                    </p>

                    <input
                        type="text"
                        id="attribute_label"
                        name="attribute_label"
                        class="regular-text"
                        required
                    >

                    <p>
                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Add attribute', 'custom-woocommerce'); ?>
                        </button>
                    </p>

                </form>
            </div>

            
                    <h2>
                        <?php esc_html_e('Existing attributes', 'custom-woocommerce'); ?>
                    </h2>

                    <table class="widefat striped" style="max-width: 900px;">
                        <thead>
                            <tr>
                                <th><?php esc_html_e('Name', 'custom-woocommerce'); ?></th>
                                <th><?php esc_html_e('Slug', 'custom-woocommerce'); ?></th>
                                <th><?php esc_html_e('Options', 'custom-woocommerce'); ?></th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (empty($attributes)) : ?>

                                <tr>
                                    <td colspan="3">
                                        <?php esc_html_e('No attributes found.', 'custom-woocommerce'); ?>
                                    </td>
                                </tr>

                            <?php else : ?>

                                <?php foreach ($attributes as $attribute) : ?>

                                    <?php
                                    $terms = $this->repository->get_terms(
                                        (int) $attribute['attribute_id']
                                    );
                                    ?>

                                    <tr>
                                        <td>
                                            <strong>
                                                <?php echo esc_html($attribute['attribute_label']); ?>
                                            </strong>
                                        </td>

                                        <td>
                                            <?php echo esc_html($attribute['attribute_name']); ?>
                                        </td>

                                        <td>
                                            <?php if (!empty($terms)) : ?>

                                                <?php
                                                $term_names = array_column($terms, 'name');
                                                echo esc_html(implode(', ', $term_names));
                                                ?>

                                            <?php else : ?>

                                                <em>
                                                    <?php esc_html_e('No options yet.', 'custom-woocommerce'); ?>
                                                </em>

                                            <?php endif; ?>

                                            <form
                                                method="post"
                                                action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                                                style="margin-top: 10px;"
                                            >
                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="cwc_create_attribute_term"
                                                >

                                                <input
                                                    type="hidden"
                                                    name="attribute_id"
                                                    value="<?php echo esc_attr($attribute['attribute_id']); ?>"
                                                >

                                                <?php wp_nonce_field('cwc_create_attribute_term'); ?>

                                                <input
                                                    type="text"
                                                    name="term_name"
                                                    placeholder="<?php esc_attr_e('Option name', 'custom-woocommerce'); ?>"
                                                    required
                                                >

                                                <button type="submit" class="button button-secondary">
                                                    <?php esc_html_e('Add option', 'custom-woocommerce'); ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                <?php endforeach; ?>

                            <?php endif; ?>
                        </tbody>
                    </table>
        </div>
        <?php
    }
}
