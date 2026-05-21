<?php

namespace WPShop\WPCommunity;

use WP_Error;

class Mail {

    public function init() {

    }

    /**
     * @param string $email
     *
     * @return bool
     * @throws \Exception
     */
    public function subscription_expire_mail( $email ) {
        $subject = __( 'Subscription on ', 'wpcommunity' ) . ' ' . get_bloginfo( 'name' );
        $message = _ob_get_content( function () {
            get_template_part( 'template-parts/mail/membership-expired' );
        } );

        return $this->send_mail( 'subscription_expire', $email, $subject, $message );
    }

    /**
     * @param string             $email
     * @param \DateTimeInterface $date date should have UTC timezome
     *
     * @return bool
     * @throws \Exception
     */
    public function scheduled_payment_mail( $email, $date ) {
        $subject = __( 'Notification of pending payment.', 'wpcommunity' ) . ' ' . get_bloginfo( 'name' );
        $message = _ob_get_content( function () use ( $date ) {
            get_template_part( 'template-parts/mail/scheduled-payment', null, [ 'date' => $date ] );
        } );

        return $this->send_mail( 'scheduled_payment', $email, $subject, $message );
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return bool
     * @throws \Exception
     */
    public function register_mail( $email, $password ) {
        $subject = __( 'Registration on', 'wpcommunity' ) . ' ' . get_bloginfo( 'name' );
        $message = _ob_get_content( function () use ( $email, $password ) {
            get_template_part( 'template-parts/mail/register', null, [
                'email'    => $email,
                'password' => $password,
            ] );
        } );

        return $this->send_mail( 'register', $email, $subject, $message );
    }

    /**
     * @param int $order_id
     *
     * @return bool|WP_Error
     * @throws \Exception
     */
    public function create_order_mail( $order_id ) {
        $orders = theme_container()->get( Orders::class );
        $order  = $orders->get_order( $order_id );

        if ( is_wp_error( $order ) ) {
            return $order;
        }

        $subject = _x( 'You placed order', 'mail order', 'wpcommunity' );
        $subject .= ' #' . $order->order_id . ' ';
        $subject .= _x( 'at', 'mail order', 'wpcommunity' );
        $subject .= ' ' . get_bloginfo( 'name' );

        $message = _ob_get_content( function () use ( $order ) {
            get_template_part( 'template-parts/mail/order-create', null, [
                'order' => $order,
            ] );
        } );

        return $this->send_mail( 'create_order', $order->user_email, $subject, $message );
    }

    /**
     * @param bool $order_id
     *
     * @return bool|WP_Error
     * @throws \Exception
     */
    public function finish_order_mail( $order_id ) {
        $orders = theme_container()->get( Orders::class );
        $order  = $orders->get_order( $order_id );

        if ( is_wp_error( $order ) ) {
            return $order;
        }

        $subject = sprintf( _x( 'Your order %s is complete', 'mail order', 'wpcommunity' ), '#' . $order->order_id );

        $message = _ob_get_content( function () use ( $order ) {
            get_template_part( 'template-parts/mail/order-finish', null, [
                'order' => $order,
            ] );
        } );

        return $this->send_mail( 'finish_order', $order->user_email, $subject, $message );
    }

    /**
     * @param string $type
     * @param string $to
     * @param string $subject
     * @param string $message
     *
     * @return bool
     * @throws \Exception
     */
    protected function send_mail( $type, $to, $subject, $message ) {
        /**
         * @since 1.2
         */
        $subject = apply_filters( 'wpcommunity/mail/subject', $subject, $type );

        /**
         * @since 1.2
         */
        $message = apply_filters( 'wpcommunity/mail/message', $message, $type );

        /**
         * @since 1.2
         */
        $unsubscribe_link = apply_filters( 'wpcommunity/mail/unsubscribe_link', '', $type );

        $utm              = [
            'utm_source'   => 'email',
            'utm_medium'   => '',
            'utm_campaign' => '',
            'utm_content'  => '',
            'utm_term'     => '',
        ];
        $allowed_utm_keys = array_keys( $utm );

        /**
         * @since 1.2
         */
        $utm = (array) apply_filters( 'wpcommunity/mail/utm', $utm, $type, $to );
        $utm = array_filter( $utm, function ( $value, $key ) use ( $allowed_utm_keys ) {
            return $value && in_array( $key, $allowed_utm_keys, true );
        }, ARRAY_FILTER_USE_BOTH );
        $utm = http_build_query( $utm );

        // Помещаем сообщение в template-parts/mail/_template-content.php
        $content = _ob_get_content( function () use ( $message, $utm, $unsubscribe_link ) {
            get_template_part( 'template-parts/mail/_template', 'content', [
                'content'          => $message,
                'utm'              => $utm,
                'unsubscribe_link' => $unsubscribe_link,
            ] );
        } );

        // Помещаем контент в template-parts/mail/_template-doc.php
        $html_doc = _ob_get_content( function () use ( $content ) {
            get_template_part( 'template-parts/mail/_template', 'doc', [
                'content' => $content,
            ] );
        } );

        $from_name  = get_bloginfo( 'name' );
        $from_email = get_bloginfo( 'admin_email' );

        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        $headers[] = 'Content-type: text/html; charset=utf-8';

        return wp_mail( $to, $subject, $html_doc, $headers );
    }
}
