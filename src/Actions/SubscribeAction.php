<?php

namespace WPShop\WPCommunity\Actions;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\Mail;
use WPShop\WPCommunity\Orders;
use WPShop\WPCommunity\PaidSubscriptions;
use function WPShop\WPCommunity\generate_username;
use function WPShop\WPCommunity\get_setting;

class SubscribeAction {

    /**
     * @var PaidSubscriptions
     */
    protected $subscriptions;

    /**
     * @var Orders
     */
    protected $orders;

    /**
     * @param PaidSubscriptions $subscriptions
     * @param Orders            $orders
     */
    public function __construct(
        PaidSubscriptions $subscriptions,
        Orders $orders
    ) {
        $this->subscriptions = $subscriptions;
        $this->orders        = $orders;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_subscription_process';
            add_action( "wp_ajax_{$action}", [ $this, '_process_subscription' ] );
            add_action( "wp_ajax_nopriv_{$action}", [ $this, '_process_subscription' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _process_subscription() {
        $data = map_deep( $_REQUEST['data'], 'wp_unslash' );
        $data = wp_parse_args( $data, [
            'email' => '',
            'id'    => '',
            'qty'   => 0,
        ] );

        // check email or logged-in user
        // если не пустая почта -- используем ее, даже если авторизован
        // если почты нет -- смотрим авторизован или нет, если нет -- выводим ошибку
        $user_id = 0;
        if ( $data['email'] ) {

            if ( ! is_email( $data['email'] ) ) {
                wp_send_json_error( new WP_Error( 'enter_email_or_authorize', __( 'Enter your email or log in.', 'wpcommunity' ) ) );
            }

            // check user exists
            $user = get_user_by( 'email', $data['email'] );

            // если нашли -- получаем user_id
            // если нет -- регистрируем
            if ( $user ) {
                $user_id = $user->ID;
            } else {

                // register user
                $username = generate_username( $data['email'] );
                $password = wp_generate_password( 20 );
                $user_id  = wp_create_user( $username, $password, $data['email'] );
                if ( is_wp_error( $user_id ) ) {
                    $attempts = 10;
                    for ( $i = 0 ; $i < $attempts ; $i ++ ) {
                        $username = generate_username( $data['email'], true );
                        $user_id  = wp_create_user( $username, $password, $data['email'] );
                        if ( ! is_wp_error( $user_id ) ) {
                            break;
                        }
                    }
                }

                if ( ! $user_id || is_wp_error( $user_id ) ) {
                    wp_send_json_error( new WP_Error( 'registerfail', __( 'Couldn&#8217;t register you, please contact the administrator!' ) ) );
                }

                // send email
                $mail = new Mail();
                $mail->register_mail( $data['email'], $password );
            }
        } else {
            if ( ! is_user_logged_in() ) {
                wp_send_json_error( new WP_Error( 'enter_email_or_authorize', __( 'Enter your email or log in.', 'wpcommunity' ) ) );
            } else {
                $user_id = get_current_user_id();
            }
        }


        // get quantity
        if ( empty( $data['qty'] ) || $data['qty'] < 1 ) {
            $qty = 1;
        } else {
            $qty = (int) $data['qty'];
        }

        // if quantity higher than maximum -- set maximum
        if ( $qty > $this->subscriptions->get_subscription_max_qty() ) {
            $qty = $this->subscriptions->get_subscription_max_qty();
        }

        $subscription = $this->subscriptions->get_subscription( $data['id'] );

        if ( ! $subscription->id ) {
            wp_send_json_error( new WP_Error( 'wrong_data', __( 'Unable to find subscription plan', 'wpcommunity' ) ) );
        }

        if ( ! get_setting( "subscription.{$subscription->id}.enabled" ) ) {
            wp_send_json_error( new WP_Error( 'wrong_data', __( 'Unable to use this subscription plan', 'wpcommunity' ) ) );
        }

//        $subscription_name = $subscription_plan['name'];
//        $price             = $subscription_plan['price'];
//        $text = $subscription_name . ' ' . $price . ' × ' . $qty . ' = ' . $total . ' user_id: ' . $user_id;


        // create order
        $order_id = $this->orders->create_order_subscription(
            $user_id,
            $subscription,
            $qty,
            $this->subscriptions->get_currency()
        );

        if ( is_wp_error( $order_id ) ) {
            wp_send_json_error( new WP_Error( $order_id->get_error_code(), $order_id->get_error_message() ) );
        }

        wp_send_json_success( [ 'order_link' => $this->orders->get_order_link( $order_id ) ] );
    }
}
