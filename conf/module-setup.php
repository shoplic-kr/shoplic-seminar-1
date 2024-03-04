<?php

if (!defined('ABSPATH')) {
    exit;
}

const PRIORITY_VERY_HIGH = 3;
const PRIORITY_HIGH      = 5;
const PRIORITY_DEFAULT   = 30;
const PRIORITY_LOW       = 50;

return [
    // Hook.
    'plugins_loaded' => [
        PRIORITY_HIGH    => [],
        PRIORITY_DEFAULT => [],
        PRIORITY_LOW     => [],
    ],
    'init'           => [
        PRIORITY_VERY_HIGH => [],
        PRIORITY_HIGH      => [],
        PRIORITY_DEFAULT   => [
			'Ajax',
            'Shortcodes',
        ],
        PRIORITY_LOW       => [],
    ],
    'admin_init'     => [
        PRIORITY_DEFAULT => []
    ],
];
