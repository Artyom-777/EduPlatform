<?php

namespace CustomWooCommerce\Core;

defined('ABSPATH') || exit;

class Deactivator
{
    /**
     * Run deactivation tasks.
     */
    public static function deactivate(): void
    {
        flush_rewrite_rules();
    }
}
