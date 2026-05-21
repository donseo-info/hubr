<?php

namespace WPShop\WPCommunity\Data;

use WPShop\WPCommunity\PaidSubscriptions;
use function WPShop\WPCommunity\theme_container;

/**
 * @property int             $order_id
 * @property string          $order_key
 * @property string          $order_link
 *
 * @property string          $title
 * @property string          $type
 * @property string          $status
 * @property string          $status_text
 * @property string          $status_text_colored
 * @property StatusHistory[] $status_history
 *
 * @property int             $user_id
 * @property int             $user_email
 *
 * @property float           $price
 * @property float           $price_total
 * @property string          $currency
 * @property string          $currency_beauty
 * @property float           $income
 * @property float           $income_currency
 * @property float           $refund
 *
 * @property string          $provider
 * @property string          $payment_type
 * @property string          $payment_id
 *
 * @property int             $subscription_id
 * @property string          $subscription_name
 * @property int             $subscription_days
 * @property int             $subscription_price
 * @property int             $qty
 *
 * @property int             $subscription_months
 */
class OrderData {

    /**
     * @var int
     * @deprecated
     */
    public $subscription_months;

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct( array $data ) {
        $this->data = $data;

        $this->subscription_months = $data['subscription_months'] ?? null;
        unset( $data['subscription_months'] );
    }

    public function __get( $name ) {
        return array_key_exists( $name, $this->data ) ? $this->data[ $name ] : null;
    }

    /**
     * @return SubscriptionPlan
     */
    public function get_subscription() {
        return theme_container()->get( PaidSubscriptions::class )->get_subscription( $this->subscription_id );
    }
}
