<?php
if (!defined('ABSPATH')) exit;

class UGC_CPT {
    public static function register() {
        register_post_type('ugc_post', [
            'labels' => [
                'name' => 'UGC Posts',
                'singular_name' => 'UGC Post',
            ],
            'public' => true,
            'show_in_rest' => true,
            'has_archive' => false,
            'rewrite' => ['slug' => 'ugc'],
            'supports' => ['title', 'editor', 'author', 'thumbnail'],
            'menu_icon' => 'dashicons-format-image',
        ]);
    }
}
