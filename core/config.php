<?php


if ( ! defined( 'WPINC' ) ) {
    die;
}

// phpcs:ignore WPThemeReview.CoreFunctionality.FileInclude.FileIncludeFound
$local = file_exists( __DIR__ . '/config.local.php' ) ? include __DIR__ . '/config.local.php' : [];

return array_replace_recursive( [
    'verify_url' => '',
    'update'     => [
        'url'          => '',
        'slug'         => 'wpcommunity',
        'check_period' => 12,
        'opt_name'     => 'wpcommunity--check-update',
    ],
], $local );
