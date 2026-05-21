<?php

/**
 * @version 1.2
 */

use WPShop\WPCommunity\Data\OrderData;
use function WPShop\WPCommunity\_ob_get_content;

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var array{'order':OrderData} $args
 */

$order = $args['order'];

?>
<p style="margin: 0 0 20px;">
    <?php echo sprintf( _x( 'Order #%s has been successfully activated.', 'mail order', 'wpcommunity' ), $order->order_id ) ?>
</p>

<?php get_template_part( 'template-parts/mail/_template', 'box', [
    'content' => _ob_get_content( function () use ( $order ) {
        ?>
        <p style="margin: 0;">
            <a href="<?php echo esc_attr( $order->order_link ) ?>" style="font-weight:bold;font-size:16px;">
                #<?php echo esc_html( $order->order_id . ' ' . $order->title ) ?>
            </a>
        </p>
        <?php
    } ),
] ); ?>

<p style="margin: 20px 0 0;">
    <?php echo sprintf( _x( 'Thanks for choosing %s.', 'mail order', 'wpcommunity' ), get_bloginfo( 'name' ) ) ?>
</p>
