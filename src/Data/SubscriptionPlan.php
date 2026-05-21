<?php

namespace WPShop\WPCommunity\Data;

/**
 * @property string $id
 * @property string $name
 * @property float  $days
 * @property float  $price
 */
class SubscriptionPlan {

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct( $id, array $data ) {
        $data['id'] = $id;
        $this->data = $data;
    }

    /**
     * @param string $name
     *
     * @return mixed|null
     */
    public function __get( $name ) {
        return array_key_exists( $name, $this->data ) ? $this->data[ $name ] : null;
    }

    /**
     * @param int $qty
     *
     * @return int
     */
    public function calc_days( $qty ) {
        return (int) ceil( $this->days * $qty );
    }
}
