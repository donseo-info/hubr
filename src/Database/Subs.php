<?php

namespace WPShop\WPCommunity\Database;

use WPShop\WPCommunity\Data\Sub;
use WPShop\WPCommunity\User;

class Subs {

    use TableNamesTrait;

    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * @param \wpdb $wpdb
     */
    public function __construct( \wpdb $wpdb ) {
        $this->wpdb = $wpdb;
    }

    /**
     * @param int    $user_id
     * @param string $target_type
     * @param int    $target
     *
     * @return int|false
     */
    public function insert( $user_id, $target_type, $target ) {
        $created_at = current_time( 'mysql' );

        return $this->wpdb->insert(
            $this->get_follows_tablename( $this->wpdb ),
            compact( 'user_id', 'target_type', 'target', 'created_at' )
        );
    }

    /**
     * @param int    $user_id
     * @param string $target_type
     * @param int    $target
     *
     * @return array|null
     */
    public function get_row( $user_id, $target_type, $target ) {
        $sql = $this->wpdb->prepare(
            "SELECT * FROM {$this->get_follows_tablename($this->wpdb)} WHERE user_id=%d AND target_type=%s AND target=%d",
            $user_id,
            $target_type,
            $target
        );

        return $this->wpdb->get_row( $sql, ARRAY_A );
    }

    /**
     * @param string $target_type
     * @param int    $target
     *
     * @return string|null
     */
    public function get_count( $target_type, $target ) {
        $sql = $this->wpdb->prepare(
            "SELECT count(*) FROM {$this->get_follows_tablename($this->wpdb)} WHERE target_type=%s AND target=%d",
            $target_type,
            $target
        );

        return $this->wpdb->get_var( $sql );
    }

    /**
     * @param int    $user_id
     * @param string $target_type
     * @param int    $target
     *
     * @return bool
     */
    public function remove( $user_id, $target_type, $target ) {
        $sql = $this->wpdb->prepare(
            "DELETE FROM {$this->get_follows_tablename($this->wpdb)} WHERE user_id=%d AND target_type=%s AND target=%d",
            $user_id,
            $target_type,
            $target
        );

        $this->wpdb->query( $sql );

        return ! $this->wpdb->last_error;
    }

    /**
     * @param int $user_id
     *
     * @return bool
     */
    public function clear_subs( $user_id ) {
        $this->wpdb->query( $this->wpdb->prepare(
            "DELETE FROM {$this->get_follows_tablename($this->wpdb)} WHERE user_id=%d",
            $user_id
        ) );

        return ! $this->wpdb->last_error;
    }

    /**
     * @param string $target_type
     * @param int    $target
     *
     * @return bool
     */
    public function clear_targets( $target_type, $target ) {
        $this->wpdb->query( $this->wpdb->prepare(
            "DELETE FROM {$this->get_follows_tablename($this->wpdb)} WHERE target_type=%s AND target=%d",
            $target_type,
            $target
        ) );

        return ! $this->wpdb->last_error;
    }


    /**
     * @param int    $user_id
     * @param string $type
     * @param bool   $merge_target_objects
     *
     * @return \Generator
     */
    public function get_rows_by_type( $user_id, $type, $merge_target_objects = false ) {

        $get_object_id = function ( $object ) {
            if ( $object instanceof \WP_User ) {
                return $object->ID;
            }
            if ( $object instanceof \WP_Term ) {
                return $object->term_id;
            }

            return null;
        };

        $limit  = 500;
        $offset = 0;
        $total  = $this->wpdb->get_var( $this->wpdb->prepare(
            "SELECT count(*) FROM {$this->get_follows_tablename($this->wpdb)} WHERE user_id=%d AND target_type=%s",
            $user_id,
            $type
        ) );
        while ( $offset < $total ) {
            $sql = $this->wpdb->prepare(
                "SELECT * FROM {$this->get_follows_tablename($this->wpdb)} WHERE user_id=%d AND target_type=%s ORDER BY created_at LIMIT %d OFFSET %d ",
                $user_id,
                $type,
                $limit,
                $offset
            );

            $results = $this->wpdb->get_results( $sql, ARRAY_A );

            if ( $merge_target_objects ) {
                $ids = array_map( function ( $row ) {
                    return $row['target'];
                }, $results );

                foreach ( $this->get_target_objects( $type, $ids ) as $object ) {
                    $object_id = (int) $get_object_id( $object );

                    foreach ( $results as $result ) {
                        if ( intval( $result['target'] ) === $object_id ) {
                            yield ( new Sub( $result ) )->set_target( $object );
                            break;
                        }
                    }
                }
            } else {
                foreach ( $results as $result ) {
                    yield new Sub( $result );
                }
            }

            $offset += $limit;

        }

        yield from [];
    }

    /**
     * @param string $type
     * @param array  $ids
     *
     * @return \WP_User[]|\WP_Term[]
     */
    protected function get_target_objects( $type, array $ids ) {
        switch ( $type ) {
            case User::FOLLOW_TYPE_USER:
                return get_users( [
                    'count_total' => false,
                    'include'     => $ids,
                ] );
            case User::FOLLOW_TYPE_CATEGORY:
                $result = get_terms( [
                    'taxonomy'   => 'category',
                    'hide_empty' => false,
                    'include'    => $ids,
                ] );
                if ( is_wp_error( $result ) ) {
                    return [];
                }

                return $result;
            case User::FOLLOW_TYPE_TAG:
                $result = get_terms( [
                    'taxonomy'   => 'post_tag',
                    'hide_empty' => false,
                    'include'    => $ids,
                ] );
                if ( is_wp_error( $result ) ) {
                    return [];
                }

                return $result;
            default:
                return [];
        }
    }
}
