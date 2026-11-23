<?php

namespace CustomWooCommerce\Core;

defined('ABSPATH') || exit;

class HookManager
{
    /**
     * Registered actions and filters.
     */
    private array $hooks = [];

    /**
     * Add an action.
     */
    public function add_action(
        string $hook,
        object $component,
        string $callback,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        $this->hooks[] = [
            'type'          => 'action',
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Add a filter.
     */
    public function add_filter(
        string $hook,
        object $component,
        string $callback,
        int $priority = 10,
        int $accepted_args = 1
    ): void {
        $this->hooks[] = [
            'type'          => 'filter',
            'hook'          => $hook,
            'component'     => $component,
            'callback'      => $callback,
            'priority'      => $priority,
            'accepted_args' => $accepted_args,
        ];
    }

    /**
     * Register all hooks.
     */
    public function register(): void
    {
        foreach ($this->hooks as $hook) {
            $callback = [
                $hook['component'],
                $hook['callback'],
            ];

            if ($hook['type'] === 'action') {
                add_action(
                    $hook['hook'],
                    $callback,
                    $hook['priority'],
                    $hook['accepted_args']
                );
            }

            if ($hook['type'] === 'filter') {
                add_filter(
                    $hook['hook'],
                    $callback,
                    $hook['priority'],
                    $hook['accepted_args']
                );
            }
        }
    }
}
