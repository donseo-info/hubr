<?php

namespace WPShop\WPCommunity\Data;

use WP_Term;
use WP_User;
use WPShop\WPCommunity\User;

/**
 * @property int    $user_id
 * @property string $target_type
 * @property int    $target
 * @property string $created_at
 */
class Sub {

    /**
     * @var array
     */
    protected $data;

    /**
     * @var null|WP_Term|WP_User
     */
    protected $_target;

    /**
     * @param array $data
     */
    public function __construct( array $data ) {
        $this->data = $data;
    }

    public function __get( $name ) {
        return array_key_exists( $name, $this->data ) ? $this->data[ $name ] : null;
    }

    /**
     * @return false|WP_Term|WP_User
     */
    public function get_target_obj() {
        if ( null === $this->_target ) {
            switch ( $this->target_type ) {
                case User::FOLLOW_TYPE_CATEGORY:
                    $term = get_term( $this->target, 'category' );
                    if ( ! is_wp_error( $term ) ) {
                        $this->_target = $term ?: false;
                    } else {
                        $this->_target = false;
                    }
                    break;
                case User::FOLLOW_TYPE_TAG:
                    $term = get_term( $this->target, 'post_tag' );
                    if ( ! is_wp_error( $term ) ) {
                        $this->_target = $term ?: false;
                    } else {
                        $this->_target = false;
                    }
                    break;
                case User::FOLLOW_TYPE_USER:
                    $this->_target = get_user_by( 'ID', $this->target );
                    break;
                default:
                    break;
            }
        }

        return $this->_target;
    }

    /**
     * @param WP_User|WP_Term $obj
     *
     * @return $this
     */
    public function set_target( $obj ) {
        switch ( $this->target_type ) {
            case User::FOLLOW_TYPE_CATEGORY:
                if ( $obj instanceof WP_Term && $obj->taxonomy === 'category' ) {
                    $this->_target = $obj;
                }
                break;
            case User::FOLLOW_TYPE_TAG:
                if ( $obj instanceof WP_Term && $obj->taxonomy === 'post_tag' ) {
                    $this->_target = $obj;
                }
                break;
            case User::FOLLOW_TYPE_USER:
                if ( $obj instanceof WP_User ) {
                    $this->_target = $obj;
                }
                break;
            default:
                break;
        }

        return $this;
    }
}
