<?php

namespace CustomWooCommerce\Products;

use CustomWooCommerce\Attributes\AttributeRepository;

defined('ABSPATH') || exit;

class ProductAttribute
{
    private AttributeRepository $repository;

    public function __construct()
    {
        $this->repository = new AttributeRepository();
    }

    /**
     * Sanitize product attributes.
     */
    public function sanitize_attributes($attributes): array
    {
        if (!is_array($attributes)) {
            return [];
        }

        $sanitized = [];

        foreach ($attributes as $attribute) {
            if (!is_array($attribute)) {
                continue;
            }

            $attribute_id = absint($attribute['attribute_id'] ?? 0);

            if (!$attribute_id) {
                continue;
            }

            $global_attributes = $this->repository->get_attributes();

            $exists = false;

            foreach ($global_attributes as $global_attribute) {
                if ((int) $global_attribute['attribute_id'] === $attribute_id) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                continue;
            }

            $available_terms = $this->repository->get_terms($attribute_id);

            $available_term_ids = array_map(
                'intval',
                array_column($available_terms, 'term_id')
            );

            $selected_term_ids = isset($attribute['options'])
                && is_array($attribute['options'])
                    ? array_map('absint', $attribute['options'])
                    : [];

            $selected_term_ids = array_values(
                array_intersect(
                    $selected_term_ids,
                    $available_term_ids
                )
            );

            if (empty($selected_term_ids)) {
                continue;
            }

            $sanitized[] = [
                'attribute_id' => $attribute_id,
                'options'      => $selected_term_ids,
                'visible'      => !empty($attribute['visible']),
                'variation'    => !empty($attribute['variation']),
            ];
        }

        return $sanitized;
    }

    /**
     * Save product attributes.
     */
    public function save_attributes(
        int $product_id,
        array $attributes
    ): bool {
        if (get_post_type($product_id) !== 'cwc_product') {
            return false;
        }

        $sanitized = $this->sanitize_attributes($attributes);

        update_post_meta(
            $product_id,
            '_cwc_product_attributes',
            $sanitized
        );

        return true;
    }

    /**
     * Get product attributes.
     */
    public function get_attributes(int $product_id): array
    {
        if (get_post_type($product_id) !== 'cwc_product') {
            return [];
        }

        $attributes = get_post_meta(
            $product_id,
            '_cwc_product_attributes',
            true
        );

        if (!is_array($attributes)) {
            return [];
        }

        foreach ($attributes as &$attribute) {
            $global_attribute = $this->get_global_attribute(
                (int) ($attribute['attribute_id'] ?? 0)
            );

            if (!$global_attribute) {
                continue;
            }

            $terms = $this->repository->get_terms(
                (int) $attribute['attribute_id']
            );

            $selected_ids = array_map(
                'intval',
                $attribute['options'] ?? []
            );

            $attribute['name'] = $global_attribute['attribute_label'];

            $attribute['options'] = array_values(
                array_filter(
                    $terms,
                    static fn($term) => in_array(
                        (int) $term['term_id'],
                        $selected_ids,
                        true
                    )
                )
            );
        }

        unset($attribute);

        return $attributes;
    }

    /**
     * Get one global attribute.
     */
    private function get_global_attribute(int $attribute_id): ?array
    {
        foreach ($this->repository->get_attributes() as $attribute) {
            if ((int) $attribute['attribute_id'] === $attribute_id) {
                return $attribute;
            }
        }

        return null;
    }
}
