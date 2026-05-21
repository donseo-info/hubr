<?php

/**
 * @version 1.0.1
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Data\SubscriptionPlan;
use function WPShop\WPCommunity\format_price;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\transform_markdown_link;

/**
 * @var array{'subscriptions':SubscriptionPlan[], 'currency':string, 'max_qty':int} $args
 */


if ( ! count( $args['subscriptions'] ) ) {
    return;
}

$profile_link = add_query_arg( [
    'redirect_to' => urlencode( get_the_permalink() ),
], get_the_permalink( get_setting( 'page.profile' ) ) );

$first_subscription = current( $args['subscriptions'] );

?>

<div class="subscriptions">
    <form action="" method="post" class="js-subscription-form">
        <div class="subscription-plans">
            <?php
            $n = 0;
            foreach ( $args['subscriptions'] as $subscription ): $n ++ ?>
                <label class="subscription-plan js-subscription-plan<?php echo $n == 1 ? ' active' : '' ?>" data-price="<?php echo esc_attr( $subscription->price ) ?>" data-currency="<?php echo esc_attr( $args['currency'] ) ?>">
                    <div class="subscription-plan__name"><?php echo esc_html( $subscription->name ) ?></div>
                    <div class="subscription-plan__price"><?php echo format_price( $subscription->price, $args['currency'] ) ?></div>
                    <input type="radio" name="id" value="<?php echo esc_attr( $subscription->id ) ?>"<?php checked( true, $n === 1 ) ?>>
                </label>
            <?php endforeach ?>
        </div><!--.subscription-plans-->
        <?php if ( ! is_user_logged_in() ): ?>
            <div class="subscription-login">
                <?php echo esc_html__( 'For payment, enter your mail:', 'wpcommunity' ) ?>
                <input name="email" type="email" class="input">
                <span><?php printf(
                        transform_markdown_link( esc_html__( 'or [log in](%s)', 'wpcommunity' ) ),
                        esc_attr( $profile_link )
                    ) ?>.</span>
            </div>
        <?php endif ?>
        <div class="subscription-summary">
            <input type="number" name="qty" min="1" max="<?php echo esc_attr( $args['max_qty'] ) ?>" step="1" class="input subscription-summary__qty js-subscription-summary-qty" value="1">
            &times;
            <span class="subscription-summary__price js-subscription-summary-price"><?php echo format_price( $first_subscription->price, $args['currency'] ) ?></span>
            =
            <strong class="subscription-summary__total js-subscription-summary-total"><?php echo format_price( $first_subscription->price, $args['currency'] ) ?></strong>
            <div class="subscription-summary__button">
                <button type="submit"><?php echo esc_html__( 'Pay', 'wpcommunity' ) ?></button>
            </div>
        </div>
    </form>
</div><!--.subscriptions-->
