<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Data\OrderData;

/**
 * @var array{'order':OrderData} $args
 */

get_header();
?>
    <div class="site-content">
        <div class="content-area">
            <main id="primary" class="site-main">
                <div class="post-card">
                    😎 <?php echo __( 'Congratulations! Your order has been successfully paid.', 'wpcommunity' ) ?>
                </div>
            </main><!-- #main -->
        </div>

        <?php get_sidebar(); ?>
    </div><!--.site-content-->

<?php
get_footer();
