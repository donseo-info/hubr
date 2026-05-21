<?php

namespace WPShop\WPCommunity\Database;

trait TableNamesTrait {

    /**
     * @param \wpdb $wpdb
     *
     * @return string
     */
    public function get_follows_tablename( \wpdb $wpdb ) {
        return $wpdb->prefix . 'wpcommunity_follows';
    }

}
