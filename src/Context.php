<?php

namespace WPShop\WPCommunity;


use WP_Error;
use WP_Post;
use WP_Term;
use WP_User;

/**
 * @property bool $is_home
 * @property bool $is_single
 * @property bool $is_singular
 * @property bool $is_category
 * @property bool $is_tag
 * @property bool $is_tax
 * @property bool $is_search
 * @property bool $is_404
 * @property bool $is_preview
 */
class Context implements \JsonSerializable {

    const OBJ_TYPE_POST = 'post';
    const OBJ_TYPE_TERM = 'term';
    const OBJ_TYPE_USER = 'user';

    /**
     * @var array
     */
    protected $conditions = [];

    /**
     * @var WP_Post|WP_Term|WP_User
     */
    protected $queried_object;

    /**
     * @var int|null
     */
    protected $object_id;

    /**
     * @var string|null
     */
    protected $object_type;

    /**
     * @var string|null
     */
    protected $object_subtype;

    /**
     * @var string|null
     */
    protected $relative_url;

    /**
     * @var bool
     */
    protected $_retrieved = false;

    /**
     * @param string $serialized
     *
     * @return Context|WP_Error
     */
    public static function createFromParams( $serialized, $used_base64 = true ) {
        if ( ! $serialized ) {
            return new WP_Error( '', __( 'Unable to get proper data from empty serialized string', 'wpcommunity' ) );
        }

        if ( $used_base64 ) {
            $serialized = base64_decode( $serialized );
        }

        $data = json_decode( $serialized, true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            return new WP_Error( '', __( 'Unable to get proper data from serialized string', 'wpcommunity' ) );
        }

        $mac = $data['_mac'] ?? '';
        unset( $data['_mac'] );
        if ( ! hash_equals( self::get_mac( $data ), $mac ) ) {
            return new WP_Error( 'wrong_context', __( 'Unable to get proper data', 'wpcommunity' ) );
        }

        $instance = new self();

        $instance->object_id      = $data['id'];
        $instance->object_type    = $data['type'];
        $instance->object_subtype = $data['subtype'];
        $instance->relative_url   = $data['url'];

        foreach (
            [
                'is_home',
                'is_single',
                'is_category',
                'is_tag',
                'is_tax',
                'is_search',
                'is_404',
                'is_preview',
            ] as $key
        ) {
            $instance->conditions[ $key ] = ! empty( $data[ $key ] );
        }

        /**
         * Allows to add additional conditions for the context
         *
         * @since 1.0
         */
        $conditions = (array) apply_filters( 'wpcommunity/context/conditions', [] );

        foreach ( $conditions as $key => $value ) {
            if ( ! array_key_exists( $key, $instance->conditions ) ) {
                $instance->conditions[ $key ] = ! empty( $data[ $key ] );
            }
        }

        return $instance;
    }

    /**
     * @return Context
     */
    public static function createFromWpQuery() {
        $instance = new self();

//        global $wp;
//        $instance->relative_url   = add_query_arg( $wp->query_vars, home_url() );
        $instance->relative_url   = home_url( $_SERVER['REQUEST_URI'] ?? '', 'relative' );
        $instance->queried_object = get_queried_object();

        $instance->retrieve_object_params();

        $instance->conditions['is_home']     = apply_filters( 'wpcommunity/context/is_home', is_home() );
        $instance->conditions['is_single']   = is_single();
        $instance->conditions['is_singular'] = is_singular();
        $instance->conditions['is_category'] = is_category();
        $instance->conditions['is_tag']      = is_tag();
        $instance->conditions['is_tax']      = is_tax();
        $instance->conditions['is_search']   = is_search();
        $instance->conditions['is_404']      = is_404();
        $instance->conditions['is_preview']  = is_preview();

        foreach ( (array) apply_filters( 'wpcommunity/context/conditions', [] ) as $key => $value ) {
            if ( ! array_key_exists( $key, $instance->conditions ) ) {
                $instance->conditions[ $key ] = $value;
            }
        }

        return $instance;
    }

    /**
     * @param int|null $id
     *
     * @return bool
     * @see is_page()
     */
    public function is_page( $id = null ) {
        if ( self::OBJ_TYPE_POST == $this->get_object_type() ) {
            if ( 'page' == $this->get_object_subtype() ) {
                if ( $id ) {
                    return $id == $this->get_object_id();
                }

                return true;
            }
        }

        return false;
    }

    /**
     * @param string|array $post_types
     *
     * @return bool
     * @see is_singular()
     */
    public function is_singular( $post_types = '' ) {
        if ( $this->is_singular ) {
            if ( $post_types && self::OBJ_TYPE_POST === $this->get_object_type() ) {
                $post_types = wp_parse_list( $post_types );

                return in_array( $this->get_object_subtype(), $post_types );
            }

            return true;
        }

        return false;
    }

    /**
     * @return int|null
     */
    public function get_object_id() {
        return $this->object_id;
    }

    /**
     * @return string|null
     */
    public function get_object_type() {
        return $this->object_type;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function is_object_type( $type ) {
        return $type === $this->object_type;
    }

    /**
     * @return string|null
     */
    public function get_object_subtype() {
        return $this->object_subtype;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function is_object_subtype( $type ) {
        return $type === $this->object_subtype;
    }

    /**
     * @return string|null
     */
    public function get_relative_url() {
        return $this->relative_url;
    }

    /**
     * @return void
     */
    protected function retrieve_object_params() {
        if ( $this->_retrieved ) {
            return;
        }

        if ( $this->queried_object instanceof WP_Post ) {
            $this->object_id      = $this->queried_object->ID;
            $this->object_type    = self::OBJ_TYPE_POST;
            $this->object_subtype = $this->queried_object->post_type;
        }
        if ( $this->queried_object instanceof WP_Term ) {
            $this->object_id      = $this->queried_object->term_id;
            $this->object_type    = self::OBJ_TYPE_TERM;
            $this->object_subtype = $this->queried_object->taxonomy;
        }
        if ( $this->queried_object instanceof WP_User ) {
            $this->object_id   = $this->queried_object->ID;
            $this->object_type = self::OBJ_TYPE_USER;
        }

        $this->_retrieved = true;
    }

    /**
     * @return string
     */
    public function __toString() {
        return base64_encode( json_encode( $this ) );
    }

    /**
     * @return array
     */
    #[\ReturnTypeWillChange]
    public function jsonSerialize() {
        $result = [];
        foreach ( $this->conditions as $key => $value ) {
            if ( $value ) {
                $result[ $key ] = $value;
            }
        }
        $result['id']      = $this->object_id;
        $result['type']    = $this->object_type;
        $result['subtype'] = $this->object_subtype;
        $result['url']     = $this->relative_url;
        $result['_mac']    = self::get_mac( $result );

        return $result;
    }

    /**
     * @param $name
     *
     * @return mixed|null
     */
    public function __get( $name ) {
        if ( array_key_exists( $name, $this->conditions ) ) {
            return $this->conditions[ $name ];
        }

        return null;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected static function get_mac( $data ) {
        asort( $data );
        $result = [];
        foreach ( $data as $key => $value ) {
            $result[] = "$key:$value";
        }

        /**
         * Allows to change used salt for context mac
         *
         * @since 1.2.0
         */
        $salt = add_filter( 'wpcommunity/context/salt', defined( 'AUTH_KEY' ) ? AUTH_KEY : '' );

        return md5( $salt . implode( '|', $result ) );
    }
}
