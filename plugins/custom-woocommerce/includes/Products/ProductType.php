<?php

namespace CustomWooCommerce\Products;

defined('ABSPATH') || exit;

class ProductType
{
    /**
     * Available product types.
     */
    public const TYPES = [
        'simple'   => 'Simple product',
        'variable' => 'Variable product',
        'grouped'  => 'Grouped product',
        'external' => 'External/Affiliate product',
    ];

    /**
     * Get all available product types.
     */
    public static function get_types(): array
    {
        return self::TYPES;
    }

    /**
     * Check whether a product type is valid.
     */
    public static function is_valid(string $type): bool
    {
        return array_key_exists($type, self::TYPES);
    }
}