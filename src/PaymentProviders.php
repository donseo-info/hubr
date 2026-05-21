<?php

namespace WPShop\WPCommunity;

use WPShop\WPCommunity\PaymentProviders\PaymentProviderInterface;
use WPShop\WPCommunity\PaymentProviders\Prodamus;
use WPShop\WPCommunity\PaymentProviders\RoboKassa;
use WPShop\WPCommunity\PaymentProviders\YooKassa;

class PaymentProviders {

    /**
     * @var array
     */
    public $_providers = [];

    /**
     * Constructor
     */
    public function __construct() {
        $this->_providers = [
            'yookassa'  => [
                'name'  => 'ЮКасса',
                'class' => YooKassa::class,
            ],
            'robokassa' => [
                'name'  => 'RoboKassa',
                'class' => RoboKassa::class,
            ],
            'prodamus'  => [
                'name'  => __( 'Prodamus', 'wpcommunity' ),
                'class' => Prodamus::class,
            ],
        ];
    }

    /**
     * @return void
     */
    public function init() {
        foreach ( $this->get_providers() as $provider => $cnf ) {
            $instance = theme_container()->get( $cnf['class'] );
            if ( ! $instance instanceof PaymentProviderInterface
            ) {
                throw new \DomainException( sprintf( 'Payment "%s" must implement %s', $provider, PaymentProviderInterface::class ) );
            }
            $instance->init( $cnf['name'] );
        }
    }

    /**
     * @param string $provider
     *
     * @return PaymentProviderInterface|null
     */
    public function get( $provider ) {
        $class = $this->get_providers()[ $provider ]['class'] ?? null;
        if ( $class && theme_container()->has( $class ) ) {
            return theme_container()->get( $class );
        }

        return null;
    }

    /**
     * @return false|string
     */
    public function get_default_provider() {
        $providers = $this->get_active_providers();

        return current( $providers );
    }

    /**
     * @return array
     */
    public function get_providers() {
        return $this->_providers;
    }

    /**
     * @return string[]
     */
    public function get_active_providers() {
        return array_filter( array_keys( $this->get_providers() ), function ( $provider ) {
            $provider = $this->get( $provider );

            return $provider && $provider->is_enabled();
        } );
    }
}
