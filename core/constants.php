<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

$theme = $orig_theme = wp_get_theme();
if ( $theme->parent() ) {
    $orig_theme = $theme->parent();
}

define( 'THEME_VERSION', $theme->get( 'Version' ) );
define( 'THEME_ORIGINAL_VERSION', $orig_theme->get( 'Version' ) );
define( 'THEME_TEXTDOMAIN', $orig_theme->get( 'TextDomain' ) );
define( 'THEME_NAME', 'WPCommunity' );
define( 'THEME_SLUG', 'wpcommunity' );
define( 'THEME_SETTINGS_PAGE', THEME_SLUG . '-settings' );
define( 'THEME_DEFAULT_NONCE_CONTEXT', 'wpcommunity-nonce' );
