<?php

namespace WPShop\WPCommunity\Data;

use DateTime;
use DateTimeZone;

/**
 * @property int    $created_at_gmt
 * @property string $old_status
 * @property string $new_status
 * @property string $user_id
 * @property string $user_name
 */
class StatusHistory {

    /**
     * @var array
     */
    protected $data;

    /**
     * @param array $data
     */
    public function __construct( array $data ) {
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
     * @return string
     * @see current_time()
     */
    public function get_created_at() {
        $datetime = DateTime::createFromFormat( 'Y-m-d H:i:s', $this->created_at_gmt, new DateTimeZone( 'UTC' ) );
        if ( $datetime ) {
            $datetime->setTimezone( wp_timezone() );

            return date_i18n(
                get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
                $datetime->getTimestamp() + $datetime->getOffset()
            );
        }

        return '';
    }
}
