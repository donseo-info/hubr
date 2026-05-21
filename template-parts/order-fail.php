<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Data\OrderData;
use WPShop\WPCommunity\Orders;
use function WPShop\WPCommunity\theme_container;

/**
 * @var array{'order':OrderData} $args
 */

get_header();
?>
    <div class="site-content">
        <div class="content-area">
            <main id="primary" class="site-main">
                <div class="post-card">
                    <p>😢 <?php echo __( 'Failed to pay for the order.', 'wpcommunity' ) ?>
                        <?php printf( __( 'You can try to <a href="%s">pay</a> again.', 'wpcommunity' ), theme_container()->get( Orders::class )->get_order_link( $args['order']->order_id ) ) ?>
                    </p>
                </div>
            </main><!-- #main -->
        </div>

        <?php get_sidebar(); ?>
    </div><!--.site-content-->

<?php
get_footer();
