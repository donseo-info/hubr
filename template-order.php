<?php
/**
 * Template Name: Order
 *
 * @package WPCommunity
 */

use WPShop\WPCommunity\Orders;
use WPShop\WPCommunity\PaidSubscriptions;
use WPShop\WPCommunity\PaymentProviders;
use function WPShop\WPCommunity\get_payment_acceptance_text;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

$orders        = theme_container()->get( Orders::class );
$subscriptions = theme_container()->get( PaidSubscriptions::class );
$payments      = theme_container()->get( PaymentProviders::class );

$payment_providers = $payments->get_active_providers();


$order_id       = ( ! empty( $_GET['id'] ) ) ? $_GET['id'] : 0;
$order_key      = ( ! empty( $_GET['k'] ) ) ? $_GET['k'] : '';
$is_valid_order = false;

if ( ! empty( $order_id ) && ! empty( $order_key ) ) {
    $order_id = base64_decode( $order_id );

    $is_valid_order = $orders->check_order_key( $order_id, $order_key );

    // ищем заказ
    $order = $orders->get_order( $order_id );
    if ( is_wp_error( $order ) ) {
        $is_valid_order = false;
    }
}

get_header();
?>

    <div class="site-content">

        <div class="content-area">

            <div class="content-area-inner"><?php /* для расположения блоков flex column с отступом gap */ ?>

                <?php

                /**
                 * Before main content hook
                 *
                 * [ru] Хук перед выводом основного контентом
                 *
                 * @hooked \WPShop\WPCommunity\Features\Breadcrumbs::_output_breadcrumbs()
                 * @hooked \WPShop\WPCommunity\DefaultHooks::_output_homepage_h1()
                 * @hooked \WPShop\WPCommunity\DefaultPages::_output_page_header(), 15
                 *
                 * @since 1.0
                 */
                do_action( 'wpcommunity/main/before', 'template-order' );
                ?>

                <main id="primary" class="site-main">

                    <div class="post-card">

                        <?php if ( ! $is_valid_order ) : ?>

                            <h1><?php _e( 'Order not found', 'wpcommunity' ) ?></h1>

                            <div class="no-access">
                                <div class="no-access__icon">😢</div>
                                <?php echo esc_html__( 'Your order may have been deleted or expired.', 'wpcommunity' ) ?>
                            </div>

                        <?php elseif ( ! $payment_providers ): ?>
                            <h1><?php echo __( 'Unable to handle payment', 'wpcommunity' ) ?></h1>

                            <div class="no-access">
                                <div class="no-access__icon">😢</div>
                                <p><?php echo __( 'There are not available payment providers', 'wpcommunity' ) ?></p>
                            </div>
                        <?php else: ?>

                            <h1>#<?php echo $order_id . ' ' . $order->title ?></h1>
                            <p><?php echo $order->status_text_colored ?></p>

                            <?php
//                        echo '<pre>';
//                        print_r( $order );
//                        echo '</pre>';
                            ?>

                            <p>
                                <?php echo $order->subscription_price ?>
                                <?php echo $order->currency_beauty ?>
                                ×
                                <?php echo $order->qty ?>
                                =
                                <?php echo $order->price ?>
                                <?php echo $order->currency_beauty ?>
                            </p>

                            <?php
                            if ( $orders->is_order_payable( $order_id ) ): ?>
                                <form action="" method="post" class="payment-form js-order-payment-form">
                                    <?php if ( count( $payment_providers ) > 1 ): ?>
                                        <div class="payment-form__description js-payment-provider-description--current">
                                            <?php $instance = $payments->get( $payments->get_default_provider() ); ?>
                                            <?php echo $instance->get_description() ?>
                                        </div>
                                        <div class="payment-providers-container">
                                            <div class="payment-providers">
                                                <?php foreach ( $payment_providers as $provider ): ?>
                                                    <?php $instance = $payments->get( $provider ); ?>
                                                    <label class="payment-provider js-payment-provider<?php echo $payments->get_default_provider() == $provider ? ' active' : '' ?>"
                                                           data-is_recurring="<?php echo absint( $instance->is_recurring_enabled() ) ?>">
                                                        <?php if ( method_exists( $instance, 'get_logo' ) ): ?>
                                                            <?php echo $instance->get_logo() ?>
                                                        <?php else: ?>
                                                            <?php echo esc_html( $instance->get_name() ) ?>
                                                        <?php endif ?>
                                                        <input type="radio" class="payment-form-provider-list__input"
                                                               name="provider"
                                                               value="<?php echo $provider ?>"<?php checked( $payments->get_default_provider(), $provider ) ?>>
                                                        <div class="js-payment-provider-description"
                                                             style="display: none">
                                                            <?php echo $instance->get_description() ?>
                                                        </div>
                                                    </label>
                                                <?php endforeach ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="payment-form__description">
                                            <?php $instance = $payments->get( $payments->get_default_provider() ); ?>
                                            <div>
                                                <?php if ( method_exists( $instance, 'get_logo' ) ): ?>
                                                    <?php echo $instance->get_logo() ?>
                                                <?php else: ?>
                                                    <?php echo esc_html( $instance->get_name() ) ?>
                                                <?php endif ?>
                                            </div>
                                            <?php echo $instance->get_description() ?>
                                        </div>
                                        <input type="hidden" name="provider"
                                               value="<?php echo $payments->get_default_provider() ?>">
                                    <?php endif ?>
                                    <div class="payment-subscription js-payment-subscription">
                                        <label class="payment-subscription__input-wrap">
                                            <input type="checkbox" name="subscribe" value="1">
                                            <?php echo wp_kses_post( get_setting( 'payment.recurring.checkbox_text' ) ) ?>
                                        </label>
                                        <?php if ( $description = get_setting( 'payment.recurring.description' ) ): ?>
                                            <div class="payment-subscription__description">
                                                <?php echo wp_kses_post( $description ) ?>
                                            </div>
                                        <?php endif ?>
                                    </div>

                                    <div><?php echo get_payment_acceptance_text() ?></div>

                                    <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ) ?>">
                                    <input type="hidden" name="order_key" value="<?php echo esc_attr( $order_key ) ?>">
                                    <div class="payment-form__footer">
                                        <button type="submit"><?php echo esc_html__( 'Pay', 'wpcommunity' ) ?></button>
                                    </div>
                                </form>
                            <?php endif ?>


                        <?php endif; ?>
                    </div>

                </main><!-- #main -->

                <?php

                /**
                 * After main content hook
                 *
                 * [ru] Хук после выводом основного контентом
                 *
                 * @hooked \WPShop\WPCommunity\Features\RelatedProducts::_output_related_posts()
                 *
                 * @since 1.0
                 */
                do_action( 'wpcommunity/main/after', 'template-order' );
                ?>

            </div><!--.content-area-inner-->

        </div>

        <?php get_sidebar(); ?>

    </div><!--.site-content-->

<?php
get_footer();
