<?php

/**
 * @version 1.3.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use function WPShop\WPCommunity\the_attributes;

?>
<nav <?php the_attributes( 'nav.main-navigation', [ 'id' => 'site-navigation', 'classes' => 'main-navigation' ] ); ?>>
    <?php
    wp_nav_menu(
        [
            'theme_location' => 'primary-menu',
            'menu_id'        => 'primary-menu',
            'fallback_cb'    => '__return_empty_string',
        ]
    );
    ?>
</nav><!-- #site-navigation -->
<div class="main-navigation--hamburger hamburger js-hamburger">
    <span></span>
    <span></span>
</div>
