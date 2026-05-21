<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\Membership;
use WPShop\WPCommunity\Orders;
use WPShop\WPCommunity\OrderStatus;
use WPShop\WPCommunity\PaymentProviders;
use WPShop\WPCommunity\PaymentProviders\RecurringPayments;

class OrderAction {

    /**
     * @var Orders
     */
    protected $orders;

    /**
     * @var PaymentProviders
     */
    protected $payment_providers;

    /**
     * @var OrderStatus
     */
    protected $order_status;

    /**
     * @var Membership
     */
    protected $membership;

    /**
     * @var RecurringPayments
     */
    protected $recurring_payments;

    /**
     * @param Orders            $orders
     * @param PaymentProviders  $payment_providers
     * @param OrderStatus       $order_status
     * @param Membership        $membership
     * @param RecurringPayments $recurring_payments
     */
    public function __construct(
        Orders $orders,
        PaymentProviders $payment_providers,
        OrderStatus $order_status,
        Membership $membership,
        RecurringPayments $recurring_payments
    ) {
        $this->orders             = $orders;
        $this->payment_providers  = $payment_providers;
        $this->order_status       = $order_status;
        $this->membership         = $membership;
        $this->recurring_payments = $recurring_payments;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_order_payment';
            add_action( "wp_ajax_{$action}", [ $this, '_create_payment' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_create_payment' ] );

            $action = 'wpcommunity_order_cancel_recurring';
            add_action( "wp_ajax_{$action}", [ $this, '_cancel_recurring' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_cancel_recurring' ] );

            $action = 'wpcommunity_admin_order_finish';
            add_action( "wp_ajax_{$action}", [ $this, '_finish_order' ] );
//            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_finish_order' ] );

            $action = 'wpcommunity_admin_change_order_status';
            add_action( "wp_ajax_{$action}", [ $this, '_change_order_status' ] );

            $action = 'wpcommunity_create_refund';
            add_action( "wp_ajax_{$action}", [ $this, '_create_refund' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _create_payment() {
        $data = wp_parse_args( $_REQUEST['data'], [
            'provider'  => '',
            'order_id'  => 0,
            'order_key' => '',
            'subscribe' => 0,
        ] );

        $provider = $data['provider'];
        $order_id = $data['order_id'];

        if ( ! $this->orders->check_order_key( $order_id, $data['order_key'] ) ) {
            wp_send_json_error( new WP_Error( 'invalid_order_key', __( 'Invalid order key.', 'wpcommunity' ) ) );
        }

        // можно ли оплачивать по статусу заказа
        if ( ! $this->orders->is_order_payable( $order_id ) ) {
            wp_send_json_error( new WP_Error( 'order_not_payable', __( 'This order not payable.', 'wpcommunity' ) ) );
        }

        // получаем провайдера для оплаты
        $payment_provider = $this->payment_providers->get( $provider );

        if ( ! $payment_provider ) {
            wp_send_json_error( new WP_Error( 'invalid_provider', __( 'Invalid provider.', 'wpcommunity' ) ) );
        }

        if ( ! $payment_provider->is_enabled() ) {
            wp_send_json_error( new WP_Error( 'invalid_provider', __( 'The provider is currently disabled.', 'wpcommunity' ) ) );
        }

        // сохраняем выбранного провайдера
        update_post_meta( $order_id, Orders::POST_META_PROVIDER, $provider );

        // сохраняем данные для оплаты по подписке
        if ( $data['subscribe'] && $payment_provider->is_recurring_enabled() ) {
            update_post_meta( $order_id, Orders::POST_META_IS_RECURRING, true );
//            $this->recurring_payments->schedule_recurring_date( $order_id );
        }

        if ( method_exists( $payment_provider, 'create_form_html' ) ) {
            $form_html = $payment_provider->create_form_html( $order_id );
            wp_send_json_success( [ 'form_html' => $form_html ] );
        } else {
            $payment_provider->create_payment( $order_id );

            if ( $payment_provider->get_error() ) {
                wp_send_json_error( $payment_provider->get_error() );
            }

            $redirect_url = $payment_provider->get_redirect_url();

            // todo потом можно проверять и виджет тоже

            if ( $redirect_url ) {
                wp_send_json_success( [ 'redirect_url' => $redirect_url ] );
            } else {
                wp_send_json_error( new WP_Error( 'invalid_redirect_url', __( 'Invalid redirect url.', 'wpcommunity' ) ) );
            }
        }

    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _cancel_recurring() {

        $data = wp_parse_args( $_REQUEST, [
            'order_id' => '',
        ] );

        $order = get_post( $data['order_id'] );
        if ( ! $order ) {
            wp_send_json_error( new WP_Error( 'wrong_order', __( 'Unable to cancel recurring payment without order', 'wpcommunity' ) ) );
        }

        if ( Orders::POST_TYPE !== $order->post_type ) {
            wp_send_json_error( new WP_Error( 'wrong_data', __( 'Unable to cancel recurring payment with wrong order data', 'wpcommunity' ) ) );
        }

        $user_id = absint( get_post_meta( $order->ID, Orders::POST_META_USER_ID, true ) );

        if ( ! $user_id || $user_id !== get_current_user_id() ) {
            wp_send_json_error( new WP_Error( 'wrong_user', __( 'Unable to cancel recurring payment with wrong user data', 'wpcommunity' ) ) );
        }

        $this->recurring_payments->cancel_recurring( $order->ID, __( 'by user', 'wpcommunity' ) );

        wp_send_json_success( [ 'message' => __( 'The automatic debit has been successfully canceled.', 'wpcommunity' ) ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _create_refund() {
        $data = wp_parse_args( $_REQUEST, [
            'nonce'    => '',
            'order_id' => '',
            'amount'   => 0,
        ] );

        if ( ! wp_verify_nonce( $data['nonce'], 'wpcommunity-nonce' ) ) {
            wp_send_json_error( new WP_Error( 'change_order_status', __( 'Forbidden', 'wpcommunity' ) ) );
        }

        $order = $this->orders->get_order( $data['order_id'] );
        if ( is_wp_error( $order ) ) {
            wp_send_json_error( $order );
        }

//        if ( ! is_numeric( $data['amount'] ) ) {
//            wp_send_json_error( new WP_Error( 'validation', __( 'Invalid refund amount', 'wpcommunity' ) ) );
//        }
//        $amount = $data['amount'] ?: $order->price;

        $amount = $order->price;

        if ( ! $this->orders->can_refund_by_provider( $order->order_id ) ||
             ! $this->order_status->can_change_status( $order->status, OrderStatus::REFUNDED )
        ) {
            wp_send_json_error( new WP_Error( 'refund', __( 'A refund cannot be created on this order.', 'wpcommunity' ) ) );
        }

        $payment_provider = $this->payment_providers->get( $order->provider );
        if ( ! $payment_provider ) {
            wp_send_json_error( new WP_Error( 'refund', __( 'Payment provider not found to creating the return.', 'wpcommunity' ) ) );
        }

        $payment_provider->refund( $order->order_id, $amount );
        if ( $error = $payment_provider->get_error() ) {
            wp_send_json_error( $error );
        }

        $this->orders->confirm_refund( $order->order_id, $amount );
        $this->membership->discard_membership( $order->user_id );

        // todo send mail notification

        wp_send_json_success();
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _finish_order() {
        $order_id = $_REQUEST['order_id'];

        $order = $this->orders->get_order( $order_id );
        if ( is_wp_error( $order ) ) {
            wp_send_json_error( $order );
        }

        $result = $this->orders->finish_order( $order_id, get_current_user_id() );
        if ( is_wp_error( $result ) ) {
            wp_send_json_error( $result );
        }

        wp_send_json_success( [ 'status' => $this->orders->get_order_status_text( $order_id ) ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _change_order_status() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( new WP_Error( 'change_order_status', __( 'You are not allowed to change order status', 'wpcommunity' ) ) );
        }

        $data = wp_parse_args( $_REQUEST, [
            'nonce'    => '',
            'order_id' => 0,
            'status'   => '',
        ] );

        if ( ! wp_verify_nonce( $data['nonce'], 'wpcommunity-nonce' ) ) {
            wp_send_json_error( new WP_Error( 'change_order_status', __( 'Forbidden', 'wpcommunity' ) ) );
        }

        $order = $this->orders->get_order( $data['order_id'] );
        if ( is_wp_error( $order ) ) {
            wp_send_json_error( $order );
        }

        $change_result = $this->order_status->change_status( $order->order_id, $data['status'] );
        if ( is_wp_error( $change_result ) ) {
            wp_send_json_error( $change_result );
        }

        // todo send mail notification

        wp_send_json_success();
    }
}
