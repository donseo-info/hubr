<?php

namespace WPShop\WPCommunity\PaymentProviders;

use WP_Error;
use WPShop\WPCommunity\Logger;
use WPShop\WPCommunity\Orders;
use function WPShop\WPCommunity\_ob_get_content;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\theme_container;

class RoboKassa implements PaymentProviderInterface {

    const QUERY_PARAM = 'robokassa';

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var bool
     */
    protected $is_test = false;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $description;

    /**
     * @var string
     */
    protected $merchant_login;

    /**
     * @var string
     */
    protected $pass1;
    /**
     * @var string
     */
    protected $pass2;

    /**
     * @var string
     */
    protected $has_algo;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var WP_Error|null
     */
    protected $error;

    /**
     * @var string|null
     */
    protected $redirect_url;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param string $merchant_login
     * @param string $pass1
     * @param string $pass2
     * @param string $has_algo
     * @param string $currency
     * @param Logger $logger
     */
    public function __construct(
        $merchant_login,
        $pass1,
        $pass2,
        $has_algo,
        $currency,
        Logger $logger
    ) {
        $this->merchant_login = $merchant_login;
        $this->pass1          = $pass1;
        $this->pass2          = $pass2;
        $this->has_algo       = $has_algo;
        $this->currency       = $currency;

        $this->logger = $logger;
    }

    /**
     * @return string|null
     */
    public function get_name() {
        return $this->name;
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

    public function get_logo() {
        return sprintf(
            '<img src="%s" alt="%s">',
            get_template_directory_uri() . '/assets/public/images/RKLogo.png',
            $this->get_name()
        );
    }

    /**
     * @return string|null
     */
    public function get_description() {
        return $this->description;
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
     * @return false|string
     * @throws \Exception
     */
    public function create_form_html( $order_id ) {
        return _ob_get_content( function ( $order_id ) {
            $orders = theme_container()->get( Orders::class );

            $order = $orders->get_order( $order_id );

            $receipt_data = [];
            if ( get_setting( 'payment.robokassa.send_receipt' ) ) {
                // sno не передаем
                $receipt_data = [
                    'items' => [
                        [
                            'name'     => '#' . $order->order_id . ' ' . $order->title,
                            'quantity' => $order->qty,
                            'sum'      => $order->subscription_price,
                            //'cost'     => $order->subscription_price,

                            'payment_object' => 'full_payment', // Полный расчет
                            'payment_method' => 'service', // Услуга
                            'tax'            => 'none', // Без НДС
                        ],
                    ],
                ];
            }

            /**
             * @since 1.3.0
             */
            $receipt_data = apply_filters( 'wpcommunity/robokassa/receipt', $receipt_data, $order_id );

            $receipt_json = null;
            if ( ! empty( $receipt_data ) ) {
                $receipt_json = ( ! empty( $receipt_data ) && \is_array( $receipt_data ) )
                    ? \urlencode( \json_encode( $receipt_data, 256 ) )
                    : null;
            }

            $signature_value = $this->get_signrature(
                $this->get_signature_string( $order->price, $order->order_id, $receipt_json ),
                $this->has_algo
            );

            ?>
            <div class="">
                <form action="https://auth.robokassa.ru/Merchant/Index.aspx" method="POST" id="<?php echo $_id = uniqid( 'robokassa.' ) ?>">
                    <input type="hidden" name="MerchantLogin" value="<?php echo $this->merchant_login ?>">
                    <input type="hidden" name="OutSum" value="<?php echo $order->price ?>">
                    <input type="hidden" name="InvId" value="<?php echo $order->order_id ?>">
                    <input type="hidden" name="Description" value="">
                    <input type="hidden" name="OutSumCurrency" value="<?php echo $this->currency ?>">
                    <input type="hidden" name="Email" value="<?php echo $order->user_email ?>">
                    <input type="hidden" name="Culture" value="<?php echo get_locale() === 'ru_RU' ? 'ru' : 'en' ?>">
                    <?php if ( $receipt_json ): ?>
                        <input type="hidden" name="Receipt" value="<?php echo $receipt_json ?>">
                    <?php endif ?>
                    <?php if ( $this->is_recurring_enabled() ): ?>
                        <input type="hidden" name="Recurring" value="1">
                    <?php endif ?>
                    <?php if ( $this->is_test() ): ?>
                        <input type="hidden" name="IsTest" value="1">
                    <?php endif ?>
                    <input type="hidden" name="SignatureValue" value="<?php echo $signature_value ?>">
                    <input type="submit" value="Оплатить">
                </form>
                <script type="text/javascript">document.getElementById('<?php echo $_id ?>').submit()</script>
            </div>
            <?php
        }, $order_id );
    }

    /**
     * @param int $order_id
     *
     * @return $this|RoboKassa
     * @throws \Exception
     */
    public function create_payment( $order_id ) {

        // сейчас не используется, вместо этого метода нужно вызывать create_form_html();

        throw new  \RuntimeException( 'The method ' . __METHOD__ . ' currently is deprecated' );

        $orders = theme_container()->get( Orders::class );

        $order = $orders->get_order( $order_id );

        $body = [
            'MerchantLogin'  => $this->merchant_login,
            'OutSum'         => $order->price,
            'InvId'          => $order->order_id,
            'OutSumCurrency' => $this->currency,
            'Description'    => '',
            'Email'          => $order->user_email,
            'Culture'        => get_locale() === 'ru_RU' ? 'ru' : 'en',
        ];

        $receipt = [];
        if ( get_setting( 'payment.robokassa.send_receipt' ) ) {
            // sno не передаем
            $receipt = [
                'items' => [
                    [
                        'name'     => '#' . $order->order_id . ' ' . $order->title,
                        'quantity' => $order->qty,
                        'sum'      => $order->subscription_price,
                        //'cost'     => $order->subscription_price,

                        'payment_object' => 'full_payment', // Полный расчет
                        'payment_method' => 'service', // Услуга
                        'tax'            => 'none', // Без НДС
                    ],
                ],
            ];
        }
        $receipt_json = null;
        if ( ! empty( $receipt ) ) {
            $receipt_json    = ( ! empty( $receipt ) && \is_array( $receipt ) )
                ? \urlencode( \json_encode( $receipt, 256 ) )
                : null;
            $body['Receipt'] = $receipt_json;
        }

        $body['SignatureValue'] = $this->get_signrature(
            $this->get_signature_string( $order->price, $order->order_id, $receipt_json ),
            $this->has_algo
        );

        if ( $this->is_recurring_enabled() ) {
            $body['Recurring'] = true;
        }


        if ( $this->is_test() ) {
            $body['IsTest'] = 1;
        }

        $this->redirect_url = 'https://auth.robokassa.ru/Merchant/Index.aspx?' . http_build_query( $body );

        return $this;

        // @see https://docs.robokassa.ru/pay-interface/
        $response = wp_remote_post( 'https://auth.robokassa.ru/Merchant/Index.aspx', [
            'timeout'     => 60,
            'redirection' => 5,
            'body'        => $body,
        ] );

        $this->logger->log( "create payment" );
        $this->logger->log( "Params: " . print_r( $body, true ) );

        if ( is_wp_error( $response ) ) {
            $this->error = $response;
        } else {
            if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $this->error = new WP_Error( 'request_error', wp_remote_retrieve_response_message( $response ) );
            } else {
                $body = wp_remote_retrieve_body( $response );

                $this->logger->log( "response body: " . $body );

                $body = \json_decode( $body, true );
                if ( JSON_ERROR_NONE !== json_last_error() ) {
                    $this->error = new WP_Error( 'parse_error', sprintf( __( 'Unable to parse response from RoboKassa: %s' ), json_last_error_msg() ) );
                } else {
                    $this->redirect_url = 'https://auth.robokassa.ru/Merchant/Index/' . $body['invoiceID'];
                }
            }
        }

        if ( $this->error ) {
            $this->logger->log( "Response error: " . $this->error->get_error_message() );
        }

        return $this;
    }

    /**
     * @param int $order_id
     * @param int $parent_order_id
     *
     * @return $this|RoboKassa
     * @throws \Exception
     */
    public function create_recurring_payment( $order_id, $parent_order_id ) {
        $orders = theme_container()->get( Orders::class );

        $order        = $orders->get_order( $order_id );
        $parent_order = $orders->get_order( $parent_order_id );

        $body = [
            'MerchantLogin'     => $this->merchant_login,
            'OutSum'            => $order->price,
            'InvoiceID'         => $order->order_id,
            'PreviousInvoiceID' => $parent_order->order_id,
            'OutSumCurrency'    => $this->currency,
            'Description'       => '',
            'SignatureValue'    => $this->get_signrature(
                $this->get_signature_string( $order->price, $order->order_id, null ),
                $this->has_algo
            ),
        ];

        $this->logger->log( "create recurring payment" );
        $this->logger->log( "Params: " . print_r( $body, true ) );

        // see https://docs.robokassa.ru/recurring/
        $response = wp_remote_post( 'https://auth.robokassa.ru/Merchant/Recurring', [
            'timeout'     => 60,
            'redirection' => 5,
            'body'        => $body,
        ] );

        if ( is_wp_error( $response ) ) {
            $this->error = $response;
        } else {
            if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
                $this->error = new WP_Error( 'request_error', wp_remote_retrieve_response_message( $response ) );
            } else {
                $body = wp_remote_retrieve_body( $response );

                $this->logger->log( "response body: " . $body );

                if ( $body != 'OK' . $order->order_id ) {
                    $this->error = new WP_Error( 'response_error', __( 'Response body: ' . $body ) );
                }

//                $body = \json_decode( $body, true );
//                if ( JSON_ERROR_NONE !== json_last_error() ) {
//                    $this->error = new WP_Error( 'parse_error', sprintf( __( 'Unable to parse response from RoboKassa: %s' ), json_last_error_msg() ) );
//                }

            }
        }

        if ( $this->error ) {
            $this->logger->log( "Response error: " . $this->error->get_error_message() );
        }

        return $this;
    }

    /**
     * @return void
     */
    protected function webhook() {
        if ( ! isset( $_REQUEST['webhook_payment'] ) ||
             $_REQUEST['webhook_payment'] !== 'robokassa'
        ) {
            return;
        }

        $this->logger->log( "start order processing" );

        $orders = theme_container()->get( Orders::class );

        $request = wp_parse_args( $_REQUEST, [
            'InvId' => '',
        ] );

        $this->logger->log( "params: " . print_r( $request, true ) );

        $order_id = $request["InvId"];

        if ( ! $order_id ) {
            $this->logger->log( "Order not found" );
            wp_redirect( get_site_url() );
            die;
        }

        $order = $orders->get_order( $order_id );

        if ( is_wp_error( $order ) ) {
            $this->logger->log( $order->get_error_message() );
            die( "order #{$order_id} not found\n" );
        }

        switch ( $_REQUEST['action'] ?? '' ) {
            case 'result':
                $sum      = $_REQUEST["OutSum"];
                $fee      = $_REQUEST['Fee'];
                $method   = $_REQUEST["PaymentMethod"];
                $currency = $_REQUEST["IncCurrLabel"];
                $crc      = strtoupper( $_REQUEST["SignatureValue"] );

                if ( $crc !== strtoupper( md5( "$sum:$order_id:{$this->pass2}" ) ) ) {
                    $this->logger->log( 'bad sign' );
                    die( "bad sign\n" );
                }

                $finish_result = $orders->finish_order( $order_id, sprintf( __( 'Payment <%s>', 'wpcommunity' ), $this->name ) );
                if ( is_wp_error( $finish_result ) ) {

                    $this->logger->log( 'failed to finish order id: ' . $order_id . PHP_EOL . implode( PHP_EOL, $finish_result->get_error_messages() ) );
                    die( "unable to change order status\n" );

                } else {
                    update_post_meta( $order_id, Orders::POST_META_INCOME, $sum - $fee );
                    update_post_meta( $order_id, Orders::POST_META_INCOME_CURRENCY, $currency );
                    update_post_meta( $order_id, Orders::POST_META_PAYMENT_TYPE, $method );
                    update_post_meta( $order_id, Orders::POST_META_PAYMENT_REFUNDABLE, false );

                    $this->logger->log( 'finish order id: ' . $order_id );
                }

                die( "OK{$order_id}\n" );
            case 'success':
                $this->logger->log( 'Success order action' );
                get_template_part( 'template-parts/order', 'success', [ 'order' => $order ] );
                die;
            case 'fail':
                $this->logger->log( 'Fail order action' );
                get_template_part( 'template-parts/order', 'fail', [ 'order' => $order ] );
                die;
            default:
                $this->logger->log( 'bad request' );
                die( "bad request\n" );
        }
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
    public function is_test( $flag = null ) {
        if ( null !== $flag ) {
            $this->is_test = (bool) $flag;
        }

        return $this->is_test;
    }

    /**
     * @return false
     */
    public function can_refund() {
        return false;
    }

    /**
     * @param int   $order_id
     * @param float $amount
     *
     * @return $this|RoboKassa
     */
    public function refund( $order_id, $amount = null ) {
        return $this;
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
     * @param float       $sum
     * @param int         $invoice_id
     * @param string|null $receipt_json
     *
     * @return string
     */
    public function get_signature_string( $sum, $invoice_id, $receipt_json ) {
        return implode(
            ':',
            array_diff(
                [
                    $this->merchant_login,
                    $sum,
                    $invoice_id,
                    $this->currency,
                    $receipt_json,
                    $this->pass1,
                ],
                [
                    false,
                    '',
                    null,
                ]
            )
        );
    }

    /**
     * @param string $string
     * @param string $method
     *
     * @return string
     * @throws \Exception
     */
    public function get_signrature( $string, $method = 'md5' ) {
        if ( in_array( $method, [ 'md5', 'ripemd160', 'sha1', 'sha256', 'sha384', 'sha512' ] ) ) {
            return strtoupper( hash( $method, $string ) );
        }

        throw new \Exception( 'Wrong Signature Method' );
    }


    /**
     * @return bool
     */
    public function is_recurring_enabled() {
        return get_setting( 'payment.robokassa.recurring' );
    }
}
