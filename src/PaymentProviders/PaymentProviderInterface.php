<?php

namespace WPShop\WPCommunity\PaymentProviders;

interface PaymentProviderInterface {

    /**
     * @return string|null
     */
    public function get_name();

    /**
     * @return string|null
     */
    public function get_description();

    /**
     * @param bool|null $flag setter if not null
     *
     * @return bool
     */
    public function is_enabled( $flag = null );

    /**
     * @return bool
     */
    public function is_recurring_enabled();

    /**
     * @param int $order_id
     *
     * @return $this
     */
    public function create_payment( $order_id );

    /**
     * @param int $order_id
     * @param int $parent_order_id
     *
     * @return $this
     */
    public function create_recurring_payment( $order_id, $parent_order_id);

    /**
     * @param int      $order_id
     * @param int|null $amount
     *
     * @return $this
     */
    public function refund( $order_id, $amount = null );

    /**
     * @return bool
     */
    public function can_refund();

    /**
     * @return string|null
     */
    public function get_redirect_url();

    /**
     * @return \WP_Error|null
     */
    public function get_error();

    /**
     * @param string $name
     *
     * @return void
     */
    public function init( $name );
}
