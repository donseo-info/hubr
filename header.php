<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link    https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package WPCommunity
 */

use function WPShop\WPCommunity\_ob_get_content;
use function WPShop\WPCommunity\the_attributes;

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
    <a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'wpcommunity' ); ?></a>

    <header <?php the_attributes( 'header.site-header', [
        'id'      => 'masthead',
        'classes' => 'site-header js-site-header',
    ] ); ?>>
        <div class="site-header-inner">
            <?php

            /**
             * Hook for header inner part
             *
             * [ru] Хук для внутренней части шапки
             *
             * @hooked \WPShop\WPCommunity\Layout\Layout::_output_header_site_branding()
             * @hooked \WPShop\WPCommunity\Layout\Layout::_output_header_navigation(), 20
             * @hooked \WPShop\WPCommunity\Layout\Layout::_output_header_search(), 30
             * @hooked \WPShop\WPCommunity\Layout\Layout::_output_header_social(), 40
             * @hooked \WPShop\WPCommunity\Layout\Layout::_output_header_html_1(), 50
             * @hooked \WPShop\WPCommunity\Layout\Layout::_output_header_html_2(), 60
             *
             * @since 1.0
             */
            do_action( 'wpcommunity/header/inner' );
            ?>
        </div>
    </header><!-- #masthead -->
