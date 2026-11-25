<?php

namespace CustomWooCommerce\Core;

use CustomWooCommerce\Database\Upgrader;
use CustomWooCommerce\Products\ProductPostType;
use CustomWooCommerce\Products\ProductTaxonomies;
use CustomWooCommerce\Products\ProductMeta;
use CustomWooCommerce\Products\ProductMetaBox;
use CustomWooCommerce\Products\ProductSaleScheduler;
use CustomWooCommerce\Products\ProductAdminColumns;
use CustomWooCommerce\Products\ProductSorting;
use CustomWooCommerce\Products\ProductSearch;
use CustomWooCommerce\Products\ProductAdminFilters;
use CustomWooCommerce\Products\ProductVariation;
use CustomWooCommerce\Products\ProductVariationMeta;
use CustomWooCommerce\Products\ProductVariationMetaBox;
use CustomWooCommerce\Products\ProductAttributeMetaBox;
use CustomWooCommerce\Admin\AttributeAdminPage;

defined('ABSPATH') || exit;

class Plugin
{
    /**
     * Hook manager instance.
     */
    private HookManager $hook_manager;
    private AssetManager $asset_manager;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->hook_manager = new HookManager();
        $this->asset_manager = new AssetManager();
    }

    /**
     * Initialize plugin.
     */
    public function init(): void
    {
        Upgrader::maybe_upgrade();
    
        $this->define_hooks();
    
        $this->hook_manager->register();
    }

    /**
     * Define plugin hooks.
     */
    private function define_hooks(): void
    {
        $this->hook_manager->add_action(
            'init',
            $this,
            'load_textdomain'
        );

        $this->hook_manager->add_action(
            'init',
            new ProductPostType(),
            'register'
        );

        $this->hook_manager->add_action(
            'init',
            new ProductTaxonomies(),
            'register'
        );
    
        $this->hook_manager->add_action(
            'admin_enqueue_scripts',
            $this->asset_manager,
            'enqueue_admin_assets'
        );
    
        $this->hook_manager->add_action(
            'wp_enqueue_scripts',
            $this->asset_manager,
            'enqueue_frontend_assets'
        );

        $this->hook_manager->add_action(
            'init',
            new ProductMeta(),
            'register'
        );

        $this->hook_manager->add_action(
            'add_meta_boxes_cwc_product',
            new ProductMetaBox(),
            'register'
        );

        $this->hook_manager->add_action(
            'save_post_cwc_product',
            new ProductMetaBox(),
            'save',
            10,
            1
        );

        $this->hook_manager->add_action(
            'admin_notices',
            new ProductMetaBox(),
            'render_sku_error_notice'
        );

        $this->hook_manager->add_filter(
            'redirect_post_location',
            new ProductMetaBox(),
            'add_sku_error_to_redirect',
            10,
            2
        );

        // Schedule product sale events when product is saved.
        $this->hook_manager->add_action(
            'save_post_cwc_product',
            new ProductSaleScheduler(),
            'schedule',
            20,
            1
        );

        // Refresh product price when scheduled event runs.
        $this->hook_manager->add_action(
            'cwc_refresh_product_price',
            new ProductSaleScheduler(),
            'refresh_product_price',
            10,
            1
        );

        $product_admin_columns = new ProductAdminColumns();

        // Register custom columns.
        $this->hook_manager->add_filter(
            'manage_cwc_product_posts_columns',
            $product_admin_columns,
            'register_columns'
        );

        // Render custom column values.
        $this->hook_manager->add_action(
            'manage_cwc_product_posts_custom_column',
            $product_admin_columns,
            'render_column',
            10,
            2
        );

        $product_sorting = new ProductSorting();

        // Make Price column sortable.
        $this->hook_manager->add_filter(
            'manage_edit-cwc_product_sortable_columns',
            $product_sorting,
            'register_sortable_columns'
        );

        // Handle price sorting.
        $this->hook_manager->add_action(
            'pre_get_posts',
            $product_sorting,
            'sort_by_price'
        );

        $product_search = new ProductSearch();
        $this->hook_manager->add_filter(
            'posts_search',
            $product_search,
            'search_by_sku',
            10,
            2
        );

        $product_admin_filters = new ProductAdminFilters();

        $this->hook_manager->add_action(
            'restrict_manage_posts',
            $product_admin_filters,
            'render_stock_filter'
        );

        $this->hook_manager->add_action(
            'pre_get_posts',
            $product_admin_filters,
            'filter_products_by_stock'
        );

        $product_variation = new ProductVariation();
        $this->hook_manager->add_action(
            'init',
            $product_variation,
            'register_post_type'
        );

        $product_variation_meta = new ProductVariationMeta();
        $this->hook_manager->add_action(
            'init',
            $product_variation_meta,
            'register_meta'
        );

        $product_variation_meta_box = new ProductVariationMetaBox();
        $this->hook_manager->add_action(
            'add_meta_boxes',
            $product_variation_meta_box,
            'register'
        );

        $this->hook_manager->add_action(
            'admin_post_cwc_create_variation',
            $product_variation_meta_box,
            'create_variation'
        );
        
        $this->hook_manager->add_action(
            'admin_notices',
            $product_variation_meta_box,
            'render_variation_notice'
        );

        $product_attribute_meta_box = new ProductAttributeMetaBox();
        $this->hook_manager->add_action(
            'add_meta_boxes',
            $product_attribute_meta_box,    
            'register'
        );

        $this->hook_manager->add_action(
            'save_post_cwc_product',
            $product_attribute_meta_box,
            'save',
            10,
            1
        );

        $attribute_admin_page = new AttributeAdminPage();

        $this->hook_manager->add_action(
            'admin_menu',
            $attribute_admin_page,
            'register_menu'
        );
        
        $this->hook_manager->add_action(
            'admin_post_cwc_create_attribute',
            $attribute_admin_page,
            'create_attribute'
        );

        $this->hook_manager->add_action(
            'admin_post_cwc_create_attribute_term',
            $attribute_admin_page,
            'create_term'
        );

    }

    /**
     * Load translations.
     */
    public function load_textdomain(): void
    {
        load_plugin_textdomain(
            'custom-woocommerce',
            false,
            dirname(CWC_PLUGIN_BASENAME) . '/languages'
        );
    }
}
