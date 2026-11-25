<?php

namespace CustomWooCommerce\Attributes;

defined('ABSPATH') || exit;

class AttributeRepository
{
    /**
     * Create a global product attribute.
     */
    public function create_attribute(string $label): int|false
    {
        global $wpdb;

        $label = sanitize_text_field($label);
        $slug = sanitize_title($label);

        if ($label === '' || $slug === '') {
            return false;
        }

        $table = $wpdb->prefix . 'cwc_attributes';

        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT attribute_id
                FROM {$table}
                WHERE attribute_name = %s",
                $slug
            )
        );

        if ($exists) {
            return false;
        }

        $inserted = $wpdb->insert(
            $table,
            [
                'attribute_name'  => $slug,
                'attribute_label' => $label,
                'attribute_order' => 0,
            ],
            ['%s', '%s', '%d']
        );

        if ($inserted === false) {
            return false;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Get all global product attributes.
     */
    public function get_attributes(): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'cwc_attributes';

        return $wpdb->get_results(
            "SELECT *
            FROM {$table}
            ORDER BY attribute_order ASC, attribute_label ASC",
            ARRAY_A
        ) ?: [];
    }

    /**
     * Add an option to an attribute.
     */
    public function add_term(int $attribute_id, string $name): int|false
    {
        global $wpdb;

        $name = sanitize_text_field($name);
        $slug = sanitize_title($name);

        if ($attribute_id <= 0 || $name === '' || $slug === '') {
            return false;
        }

        $attributes_table = $wpdb->prefix . 'cwc_attributes';
        $terms_table = $wpdb->prefix . 'cwc_attribute_terms';

        $attribute_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT attribute_id
                FROM {$attributes_table}
                WHERE attribute_id = %d",
                $attribute_id
            )
        );

        if (!$attribute_exists) {
            return false;
        }

        $term_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT term_id
                FROM {$terms_table}
                WHERE attribute_id = %d AND slug = %s",
                $attribute_id,
                $slug
            )
        );

        if ($term_exists) {
            return false;
        }

        $inserted = $wpdb->insert(
            $terms_table,
            [
                'attribute_id' => $attribute_id,
                'name'         => $name,
                'slug'         => $slug,
                'term_order'   => 0,
            ],
            ['%d', '%s', '%s', '%d']
        );

        if ($inserted === false) {
            return false;
        }

        return (int) $wpdb->insert_id;
    }

    /**
     * Get all options belonging to an attribute.
     */
    public function get_terms(int $attribute_id): array
    {
        global $wpdb;

        $table = $wpdb->prefix . 'cwc_attribute_terms';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT *
                FROM {$table}
                WHERE attribute_id = %d
                ORDER BY term_order ASC, name ASC",
                $attribute_id
            ),
            ARRAY_A
        ) ?: [];
    }
}
