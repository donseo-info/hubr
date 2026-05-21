<?php

namespace WPShop\WPCommunity\PaymentProviders;

use DateTimeZone;
use DateTimeImmutable;
use WPShop\WPCommunity\Logger;
use WPShop\WPCommunity\Mail;
use WPShop\WPCommunity\Orders;
use WPShop\WPCommunity\OrderStatus;
use WPShop\WPCommunity\PaymentProviders;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

class RecurringPayments {

    const ERROR_CANCELED_ON_PAYMENT = 'canceled_on_payment';

    /**
     * @var Orders
     */
    protected $orders;

    /**
     * @var PaymentProviders
     */
    protected $payment_providers;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Orders           $orders
     * @param PaymentProviders $payment_providers
     * @param Logger           $logger
     */
    public function __construct(
        Orders $orders,
        PaymentProviders $payment_providers,
        Logger $logger
    ) {
        $this->orders            = $orders;
        $this->payment_providers = $payment_providers;
        $this->logger            = $logger;
    }

    /**
     * @param int $user_id
     *
     * @return string[]
     * @throws \Exception
     */
    public function get_recurring_dates( $user_id ) {
        $orders = get_posts( [
            'posts_per_page' => - 1,
            'post_type'      => Orders::POST_TYPE,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => Orders::POST_META_USER_ID,
                    'compare' => '=',
                    'value'   => $user_id,
                ],
                [
                    'key'     => Orders::POST_META_RECURRING_DATE,
                    'compare' => '>',
                    'value'   => 0,
                ],
                [
                    'key'     => Orders::POST_META_STATUS,
                    'compare' => '=',
                    'value'   => OrderStatus::COMPLETED,
                ],
            ],
            'orderby'        => 'ID',
            'order'          => 'DESC',
        ] );

        $dates = [];
        foreach ( $orders as $order ) {
            $date     = get_post_meta( $order->ID, Orders::POST_META_RECURRING_DATE, true );
            $datetime = new \DateTimeImmutable( '@' . $date, new DateTimeZone( 'UTC' ) );
            $datetime = $datetime->setTimezone( wp_timezone() );

            $dates[] = [
                $order->ID,
                date_i18n(
                    get_option( 'date_format' ),
                    $datetime->getTimestamp() + $datetime->getOffset()
                ),
            ];
        }

        /**
         * @since 1.1
         */
        $dates = apply_filters( 'wpcommunity/recurring_payment/subscription_expire_dates', $dates, $user_id, $orders );

//        $datetime = new \DateTimeImmutable( '@1754639472', new DateTimeZone( 'UTC' ) );
//        $datetime = $datetime->setTimezone( wp_timezone() );
//
//        $dates[] = [
//            1,
//            date_i18n(
//                get_option( 'date_format' ),
//                $datetime->getTimestamp() + $datetime->getOffset()
//            ),
//        ];

        return $dates;
    }

    /**
     * @param int $order_id
     *
     * @return $this
     * @throws \Exception
     */
    public function schedule_recurring_date( $order_id ) {
        $order = theme_container()->get( Orders::class )->get_order( $order_id );;

        $timezone = new DateTimeZone( 'UTC' );
        $datetime = new DateTimeImmutable( 'now', $timezone );
        $datetime = $datetime->modify( '+' . ( $order->subscription_days * 24 * 60 * 60 ) . ' seconds' );

        update_post_meta( $order_id, Orders::POST_META_RECURRING_DATE, $datetime->getTimestamp() );

        $this->update_history(
            $order_id,
            sprintf(
                __( 'The next subscription debit is scheduled for %s', 'wpcommunity' ),
                wp_date( 'j F Y H:i:s T', $datetime->getTimestamp(), $timezone ),
            )
        );

        return $this;
    }

    /**
     * @param int    $order_id
     * @param string $reason
     *
     * @return $this
     */
    public function cancel_recurring( $order_id, $reason = null ) {
        delete_post_meta( $order_id, Orders::POST_META_RECURRING_DATE );
        $this->update_history(
            $order_id,
            $reason ? sprintf( __( 'Subscription canceled: %s', 'wpcommunity' ), $reason ) : __( 'Subscription canceled', 'wpcommunity' )
        );

        return $this;
    }

    /**
     * @param int    $order_id
     * @param string $text_row
     *
     * @return void
     */
    protected function update_history( $order_id, $text_row ) {
        $history = (array) ( get_post_meta( $order_id, Orders::POST_META_RECURRING_HISTORY, true ) ?: [] );

        $history = array_filter( $history );

        $history[] = [
            'created_at_gmt' => current_time( 'mysql', true ),
            'user_id'        => get_current_user_id(),
            'text'           => $text_row,
        ];

        update_post_meta( $order_id, Orders::POST_META_RECURRING_HISTORY, $history );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function create_scheduled_payments() {
        $timezone = new DateTimeZone( 'UTC' );
        $datetime = new DateTimeImmutable( 'now', $timezone );

        $this->logger->log( 'start create scheduled payments' );

        $orders = $this->get_scheduled_orders( $datetime );

        $this->logger->log( 'found orders: ' . count( $orders ) );

        foreach ( $orders as $order ) {
            $this->create_scheduled_order_with_payment( $order->ID );
        }

        $this->logger->log( 'finish create scheduled payments' );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function notify_scheduled_payments() {
        $timezone = new DateTimeZone( 'UTC' );
        $datetime = new DateTimeImmutable( 'now', $timezone );

        $datetime = $datetime->modify( '+1 day' );

        $datetime = apply_filters( 'wpcommunity/recurring_payment/notify_scheduled_date', $datetime );

        $mailer = theme_container()->get( Mail::class );

        add_filter( 'wpcommunity/recurring_payment/scheduled_orders_args', $hook = function ( $args ) {
            $args['meta_query'][] = [
                'key'     => Orders::POST_META_RECURRING_NOTIFIED,
                'compare' => 'NOT EXISTS',
            ];

            return $args;
        } );

        $this->logger->log( 'start create scheduled notifications' );

        $orders = $this->get_scheduled_orders( $datetime );

        $this->logger->log( 'found orders: ' . count( $orders ) );

        foreach ( $orders as $order ) {

            $user_id        = get_post_meta( $order->ID, Orders::POST_META_USER_ID, true );
            $scheduled_date = get_post_meta( $order->ID, Orders::POST_META_RECURRING_DATE, true );

            $datetime = new \DateTimeImmutable( '@' . $scheduled_date, new DateTimeZone( 'UTC' ) );

            if ( $user = get_userdata( $user_id ) ) {
                $mailer->scheduled_payment_mail( $user->user_email, $datetime );
            }

            update_post_meta( $order->ID, Orders::POST_META_RECURRING_NOTIFIED, 1 );
        }

        remove_filter( 'wpcommunity/recurring_payment/scheduled_orders_args', $hook );

        $this->logger->log( 'finish create scheduled notifications' );
    }

    /**
     * @param int $parent_order_id
     *
     * @return void
     * @throws \Exception
     */
    public function create_scheduled_order_with_payment( $parent_order_id ) {
        // создаем повторный заказ
        $new_order_id = $this->orders->create_recurring_order( $parent_order_id );

        $parent_order = $this->orders->get_order( $parent_order_id );

        if ( is_wp_error( $new_order_id ) ) {
            $this->update_history(
                $parent_order_id,
                sprintf(
                    __( 'Unable to create recurring order: %s', 'wpcommunity' ),
                    $new_order_id->get_error_message()
                )
            );

            $this->logger->log( 'Error: ' . $new_order_id->get_error_message() );

            return;
        }

        $this->update_history(
            $parent_order_id,
            sprintf(
                __( 'Child order successfully created [%d]', 'wpcommunity' ),
                $new_order_id
            ),
        );

        // store children ids in parent order
        $children   = (array) ( get_post_meta( $parent_order_id, Orders::POST_META_RECURRING_CHILDREN, true ) ?: [] );
        $children[] = $new_order_id;
        update_post_meta( $parent_order_id, Orders::POST_META_RECURRING_CHILDREN, $children );

        // store parent id in child order
        update_post_meta( $new_order_id, Orders::POST_META_RECURRING_PARENT, $parent_order_id );

        // запрашиваем оплату заказа

        $provider         = $parent_order->provider;
        $payment_provider = $this->payment_providers->get( $provider );

        if ( ! $payment_provider ) {
            $this->logger->log( __( 'Unable to find payment provider.', 'wpcommunity' ) );

            return;
        }

        if ( ! $payment_provider->is_enabled() ) {
            $this->logger->log( __( 'Unable to create a payment for disabled provider.', 'wpcommunity' ) );

            return;
        }

        if ( ! $payment_provider->is_recurring_enabled() ) {
            $this->logger->log( __( 'Unable to create a recurring payment due to disabling reasons.', 'wpcommunity' ) );

            return;
        }

        update_post_meta( $new_order_id, Orders::POST_META_PROVIDER, $provider );

        $payment_provider->create_recurring_payment( $new_order_id, $parent_order_id );

        if ( $error = $payment_provider->get_error() ) {

            $this->logger->log( 'Error: ' . $error->get_error_message() );

            $error_limit = min( 1000, max( 0, get_setting( 'payment.recurring.errors_limit' ) ) );

            if ( $error->get_error_code() === static::ERROR_CANCELED_ON_PAYMENT ) {
                $this->cancel_recurring( $parent_order_id, __( 'by user on payment method side', 'wpcommunity' ) );
            } else {
                $errors_count = absint( get_post_meta( $parent_order_id, Orders::POST_META_RECURRING_ERRORS, true ) );
                if ( $errors_count > $error_limit ) {
                    // в случае превышения лимита ошибок автоматически отменяем заказ
                    $this->cancel_recurring( $parent_order_id, __( 'error limit is exceeded', 'wpcommunity' ) );
                } else {
                    update_post_meta( $parent_order_id, Orders::POST_META_RECURRING_ERRORS, ++ $errors_count );
                }
            }
        } else {
            $this->update_history( $parent_order_id, __( 'Successful scheduled debit', 'wpcommunity' ) );
            $this->schedule_recurring_date( $parent_order_id );
        }
    }

    /**
     * @param \DateTimeInterface $datetime
     *
     * @return \WP_Post[]
     * @throws \Exception
     */
    protected function get_scheduled_orders( $datetime ) {
        $args = [
            'posts_per_page' => - 1,
            'post_type'      => Orders::POST_TYPE,
            'meta_query'     => [
                'relation' => 'AND',
                [
                    'key'     => Orders::POST_META_RECURRING_DATE,
                    //'type'    => 'UNSIGNED',
                    'compare' => '<=',
                    'value'   => $datetime->getTimestamp(),
                ],
                [
                    'key'     => Orders::POST_META_STATUS,
                    'compare' => '=',
                    'value'   => OrderStatus::COMPLETED,
                ],
            ],
            'orderby'        => 'ID',
            'order'          => 'ASC',
        ];

        /**
         * @since 1.0
         */
        $args = apply_filters( 'wpcommunity/recurring_payment/scheduled_orders_args', $args );

        return get_posts( $args );
    }

    /**
     * @param array $args
     *
     * @return void
     */
    protected function get_posts( $args = null ) {
        $defaults = [
            'numberposts'      => 5,
            'category'         => 0,
            'orderby'          => 'date',
            'order'            => 'DESC',
            'include'          => [],
            'exclude'          => [],
            'meta_key'         => '',
            'meta_value'       => '',
            'post_type'        => 'post',
            'suppress_filters' => true,
        ];

        $parsed_args = wp_parse_args( $args, $defaults );
        if ( empty( $parsed_args['post_status'] ) ) {
            $parsed_args['post_status'] = ( 'attachment' === $parsed_args['post_type'] ) ? 'inherit' : 'publish';
        }
        if ( ! empty( $parsed_args['numberposts'] ) && empty( $parsed_args['posts_per_page'] ) ) {
            $parsed_args['posts_per_page'] = $parsed_args['numberposts'];
        }
        if ( ! empty( $parsed_args['category'] ) ) {
            $parsed_args['cat'] = $parsed_args['category'];
        }
        if ( ! empty( $parsed_args['include'] ) ) {
            $incposts                      = wp_parse_id_list( $parsed_args['include'] );
            $parsed_args['posts_per_page'] = count( $incposts );  // Only the number of posts included.
            $parsed_args['post__in']       = $incposts;
        } elseif ( ! empty( $parsed_args['exclude'] ) ) {
            $parsed_args['post__not_in'] = wp_parse_id_list( $parsed_args['exclude'] );
        }

        $parsed_args['ignore_sticky_posts'] = true;
        $parsed_args['no_found_rows']       = true;

        $get_posts = new \WP_Query();

        $result = $get_posts->query( $parsed_args );

        var_dump( $get_posts->request );
        die;

        return $result;
    }
}
