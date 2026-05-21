<?php

namespace WPShop\WPCommunity\PaymentProviders;

use Exception;
use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WPShop\WPCommunity\Logger;
use WPShop\WPCommunity\Orders;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

/**
 * Test data:
 * <code>
 * 4111 1111 1111 1111
 * 2024/12
 * CVV 123
 * 3-D Secure 12345678
 * </code>
 */
class Prodamus implements PaymentProviderInterface {

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var bool
     */
    protected $demo_mode = false;

    /**
     * @var string
     */
    protected $secret_key;

    /**
     * @var string
     */
    protected $payment_url;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var string|null
     */
    protected $redirect_url;

    /**
     * @var WP_Error|null
     */
    protected $error;

    /**
     * @param string $secret_key
     * @param string $payment_url
     * @param Logger $logger
     */
    public function __construct( $secret_key, $payment_url, Logger $logger ) {
        $this->secret_key  = $secret_key;
        $this->payment_url = $payment_url;
        $this->logger      = $logger;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function init( $name ) {
        $this->name = $name;
        add_action( 'parse_request', function () {
            if ( $this->is_enabled() ) {
                $this->webhook();
            }
        } );
    }

    /**
     * @param int $order_id
     *
     * @return $this|Prodamus
     */
    public function create_payment( $order_id ) {
        $orders = theme_container()->get( Orders::class );
        $order  = $orders->get_order( $order_id );

        $params = [
            'order_id'        => $order->order_id,
            'currency'        => strtolower( $order->currency ),
            'customer_email'  => $order->user_email,
            //'npd_income_type' => 'FROM_INDIVIDUAL',
            'do'              => 'pay',
            'sys'             => 'wordpress2',
            'urlNotification' => add_query_arg(
                [
                    'webhook_payment' => 'prodamus',
                    'order_id'        => $order_id,
                ],
                get_site_url() ),
            'urlSuccess'      => get_site_url(),
            'urlReturn'       => get_setting( 'page.join' ) ? get_permalink( get_setting( 'page.join' ) ) : get_site_url(),
        ];


        $params['products'][] = [
            'name'     => $order->title,
            'price'    => $order->price,
            'quantity' => 1,
        ];

        if ( $this->is_demo_mode() ) {
            $params['demo_mode'] = 1;
        }

        $params = apply_filters( 'wpcommunity/prodamus/created_order_params', $params );

        $params['signature'] = $this->create_hmac( $params );

        $this->redirect_url = $this->payment_url . ( strpos( $this->payment_url, '?' ) === false ? '?' : '&' ) . http_build_query( $params, "", "&" );

        $this->logger->log( "create payment" );
        $this->logger->log( "Params: " . print_r( $params, true ) );

        return $this;
    }

    /**
     * @param int $order_id
     * @param int $parent_order_id
     *
     * @return $this|Prodamus
     */
    public function create_recurring_payment( $order_id, $parent_order_id ) {
        // TODO: Implement create_recurring_payment() method.

        return $this;
    }

    /**
     * @return void
     */
    #[NoReturn]
    protected function webhook() {
        if ( ! isset( $_REQUEST['webhook_payment'] ) ||
             $_REQUEST['webhook_payment'] !== 'prodamus'
        ) {
            return;
        }
        $this->logger->log( "start order processing" );

        $orders = theme_container()->get( Orders::class );

        $headers         = wp_parse_args( getallheaders(), [
            'Sign' => '',
        ] );
        $headers['sign'] = isset( $headers['sign'] ) ? $headers['sign'] : $headers['Sign'];

        $payform_data = wp_parse_args( $_POST, [
            'payment_status' => '',
            'order_num'      => '',
            'commission'     => '',
            'commission_sum' => '',
            'payment_type'   => '',
        ] );

        if ( $payform_data['payment_status'] == 'success' ) {
            try {
                if ( ! $headers['sign'] ) {
                    throw new Exception( "Отсутствует подпись запроса" );
                }
                if ( ! $this->verify_hmac( $payform_data, $headers['sign'] ) ) {
                    throw new Exception( "Ошибка подписи передаваемых данных" );
                }
                if ( ! $payform_data['order_num'] ) {
                    throw new Exception( "Отсутствует номер заказа" );
                }
                if ( $payform_data["order_num"] != $_GET["order_id"] ) {
                    throw new Exception( "Ошибка передаваемых данных. Не совпадает ID заказа" );
                }

                $order = $orders->get_order( $payform_data['order_num'] );
                if ( is_wp_error( $order ) ) {
                    $this->logger->log( $order->get_error_message() );
                    throw new Exception( "Заказ не найден" );
                }

                $finish_result = $orders->finish_order( $order->order_id, sprintf( __( 'Payment <%s>', 'wpcommunity' ), $this->name ) );
                if ( is_wp_error( $finish_result ) ) {
                    $this->logger->log( 'failed to finish order id: ' . $order->order_id . PHP_EOL . implode( PHP_EOL, $finish_result->get_error_messages() ) );
                    throw new Exception( "Не удалось сменить статус заказа" );
                }

                update_post_meta( $order->order_id, Orders::POST_META_INCOME, $order->price - $payform_data['commission_sum'] );
                update_post_meta( $order->order_id, Orders::POST_META_INCOME_CURRENCY, $order->currency );
                update_post_meta( $order->order_id, Orders::POST_META_PAYMENT_TYPE, $payform_data['payment_type'] );
                update_post_meta( $order->order_id, Orders::POST_META_PAYMENT_REFUNDABLE, false );

                $this->logger->log( 'finish order id: ' . $order->order_id );

                die( "success" );
            } catch ( Exception $e ) {
                $this->logger->log( "Exception: " . $e->getMessage() );
                die( $e->getMessage() );
            }
        } else {
            $this->logger->log( "bad request" );
            die( "bad request\n" );
        }
    }

    /**
     * @return string
     */
    public function get_name() {
        return $this->name;
    }

    /**
     * @return string|null
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function set_description( $description ) {
        $this->description = $description;

        return $this;
    }

    /**
     * @param bool $flag
     *
     * @return bool
     */
    public function is_enabled( $flag = null ) {
        if ( null !== $flag ) {
            $this->enabled = (bool) $flag;
        }

        return $this->enabled;
    }

    /**
     * @param bool $flag
     *
     * @return bool
     */
    public function is_demo_mode( $flag = null ) {
        if ( null !== $flag ) {
            $this->demo_mode = (bool) $flag;
        }

        return $this->demo_mode;
    }

    public function refund( $order_id, $amount = null ) {
        throw new \Exception( 'Unable to handle prodamus refund' );
    }

    /**
     * @return bool
     */
    public function can_refund() {
        return false;
    }

    /**
     * @return string|null
     */
    public function get_redirect_url() {
        return $this->redirect_url;
    }

    /**
     * @return WP_Error|null
     */
    public function get_error() {
        return $this->error;
    }

    /**
     * @param array  $data
     * @param string $given_sign
     *
     * @return bool
     */
    protected function verify_hmac( $data, $given_sign ) {
        $sign = $this->create_hmac( $data );

        return $sign && strtolower( $sign ) === strtolower( $given_sign );
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function create_hmac( $data ) {
        $data = (array) $data;
        array_walk_recursive( $data, function ( &$v ) {
            $v = strval( $v );
        } );
        $this->sort( $data );
        if ( version_compare( PHP_VERSION, "5.4.0", "<" ) ) {
            $data = preg_replace_callback(
                "/((\\\u[01-9a-fA-F]{4})+)/",
                function ( $matches ) {
                    return json_decode( '"' . $matches[1] . '"' );
                },
                json_encode( $data )
            );
        } else {
            $data = json_encode( $data, JSON_UNESCAPED_UNICODE );
        }

        return hash_hmac( "sha256", $data, $this->secret_key );
    }

    /**
     * @param array $data
     *
     * @return void
     */
    protected function sort( &$data ) {
        ksort( $data, SORT_REGULAR );
        foreach ( $data as &$arr ) {
            is_array( $arr ) && $this->sort( $arr );
        }
    }

    /**
     * @return bool
     */
    public function is_recurring_enabled() {
        return get_setting( 'payment.prodamus.recurring' );
    }
}
