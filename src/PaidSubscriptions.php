<?php

namespace WPShop\WPCommunity;

use WPShop\WPCommunity\Data\SubscriptionPlan;

class PaidSubscriptions {

    const DAYS_IN_MONTH = 30.4167;
    const DAYS_IN_YEAR  = 365.25;

    /**
     * Subscription plans
     *
     * @var array|array[]
     */
    public $subscriptions = [];

    /**
     * @var string
     */
    public $currency;

    /**
     * @var int
     */
    public $subscription_max_qty;

    /**
     * Constructor
     */
    public function __construct() {
        $additional_subscriptions = (array) apply_filters( 'wpcommunity/subscriptions/additional_items', [] );

        /**
         * Allows to change subscription items order and structure
         *
         * @since 1.0
         */
        $this->subscriptions = apply_filters(
            'wpcommunity/subscriptions/items',
            array_merge( $additional_subscriptions, [
                '1_day'   => [
                    'name'  => _x( '1 day', 'subscription', 'wpcommunity' ),
                    'days'  => 1,
                    'price' => get_setting( 'subscription.1_day' ),
                ],
                '1_month' => [
                    'name'  => _x( '1 month', 'subscription', 'wpcommunity' ),
                    'days'  => self::DAYS_IN_MONTH,
                    'price' => get_setting( 'subscription.1_month' ),
                ],
                '1_year'  => [
                    'name'  => _x( '1 year', 'subscription', 'wpcommunity' ),
                    'days'  => self::DAYS_IN_YEAR,
                    'price' => get_setting( 'subscription.1_year' ),
                ],
            ] )
        );

        $this->currency = 'RUB';

        // максимум сколько можно купить подписки
        $this->subscription_max_qty = 10;

    }

    public function init() {
        add_shortcode( 'subscriptions', [ $this, 'shortcode_subscriptions' ] );
        add_action( 'wp', [ $this, '_redirect_on_profile' ] );
    }

    /**
     * Redirect on profile page (or home page) of there are not subscription plans
     *
     * @return void
     */
    public function _redirect_on_profile() {
        if ( ! is_page( get_setting( 'page.join' ) ) ) {
            return;
        }

        $subscriptions = $this->get_enabled_subscriptions();


        if ( ! $subscriptions ) {
            $redirect_to = home_url();
            if ( $page = get_post( get_setting( 'page.profile' ) ) ) {
                $redirect_to = get_permalink( $page->ID );
            }

            /**
             * Allows to set redirect url if there are no plans
             *
             * @since 1.0
             */
            $redirect_to = apply_filters( 'wpcommunity/subscriptions/redirect_to', $redirect_to );

            if ( $redirect_to ) {
                wp_redirect( $redirect_to );
                die;
            }
        }
    }

    /**
     * @return SubscriptionPlan[]
     */
    protected function get_enabled_subscriptions() {
        $subscriptions = $this->get_subscriptions();

        return array_filter( $subscriptions, function ( $subscription ) {
            return get_setting( "subscription.{$subscription->id}.enabled" );
        } );
    }

    /**
     * Get subscription plans
     *
     * @return SubscriptionPlan[]
     */
    public function get_subscriptions() {
        return array_map( function ( $item, $id ) {
            return new SubscriptionPlan( $id, $item );
        }, $this->subscriptions, array_keys( $this->subscriptions ) );
    }

    /**
     * @param string $subscription_id
     *
     * @return SubscriptionPlan
     */
    public function get_subscription( $subscription_id ) {
        return array_key_exists( $subscription_id, $this->subscriptions )
            ? new SubscriptionPlan( $subscription_id, $this->subscriptions[ $subscription_id ] )
            : new SubscriptionPlan( null, [] );
    }

    /**
     * @return int
     */
    public function get_subscription_max_qty() {
        return $this->subscription_max_qty;
    }

    /**
     * @param string $subscription_id
     * @param int    $qty
     *
     * @return string
     */
    public function get_subscription_name_with_qty( $subscription_id, $qty ) {
        $subscription = $this->get_subscription( $subscription_id );

        if ( ! $subscription->id ) {
            return '';
        }

        $days = $subscription->calc_days( $qty );

        $start = new \DateTimeImmutable( 'now' );
        $end   = $start->modify( "+{$days} days" );
        $diff  = $end->diff( $start );

        $result = [];
        if ( $diff->y ) {
            $result[] = $diff->y . ' ' . _n( 'year', 'years', $diff->y, 'wpcommunity' );
        }
        if ( $diff->m ) {
            $result[] = $diff->m . ' ' . _n( 'month', 'months', $diff->m, 'wpcommunity' );
        }
        if ( $diff->d ) {
            $result[] = $diff->d . ' ' . _n( 'day', 'days', $diff->d, 'wpcommunity' );
        }

        return implode( ' ', $result );
    }

    public function get_currency() {
        return $this->currency;
    }

    public function shortcode_subscriptions( $atts, $content = null ) {
        return _ob_get_content( function () {
            get_template_part( 'template-parts/elements/subscription-form', '', [
                'subscriptions' => $this->get_enabled_subscriptions(),
                'currency'      => $this->get_currency(),
                'max_qty'       => $this->get_subscription_max_qty(),
            ] );
        } );
    }

    public function can_user_buy_subscription( $user_id ) {
        // todo проверка например на заблокированного пользователя

        return true;
    }
}
