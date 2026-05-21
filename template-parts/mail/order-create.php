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
    <?php echo _x( 'Order', 'mail order', 'wpcommunity' ) ?>
    <?php echo esc_html( $order->title ) ?>
    <?php echo _x( 'has been successfully created.', 'mail order', 'wpcommunity' ) ?>
</p>
<p style="margin: 0 0 20px;">
    <?php echo _x( 'You can track the status, pay for the order or choose a payment method on the page:', 'mail order', 'wpcommunity' ) ?>
</p>

<?php get_template_part( 'template-parts/mail/_template', 'box', [
    'content' => _ob_get_content( function () use ( $order ) {
        ?>
        <p style="margin: 0;">
            <a href="<?php echo esc_attr( $order->order_link ) ?>" style="font-weight:bold;font-size:16px;">
                <?php echo _x( 'Your order', 'mail order', 'wpcommunity' ) ?>
            </a>
        </p>
        <?php
    } ),
] ); ?>
