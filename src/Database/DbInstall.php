<?php

namespace WPShop\WPCommunity\Database;

class DbInstall {

    use TableNamesTrait;

    /**
     * @var \wpdb
     */
    protected $wpdb;

    /**
     * @var string
     */
    protected $version_opt_name;

    /**
     * @param \wpdb $wpdb
     */
    public function __construct( \wpdb $wpdb ) {
        $this->wpdb             = $wpdb;
        $this->version_opt_name = '_wpcommunity-db';
    }

    /**
     * @return void
     */
    public function init() {
        if ( ! get_option( $this->version_opt_name, false ) ) {
            $this->install();
            update_option( $this->version_opt_name, '1.0' );
        }
    }

    /**
     * @return void
     */
    protected function install() {
        $table_name      = $this->get_follows_tablename( $this->wpdb );
        $charset_collate = $this->wpdb->get_charset_collate();

        $sql = <<<"SQL"
CREATE TABLE IF NOT EXISTS $table_name (
    user_id bigint  NOT NULL,
    target_type varchar(50) NOT NULL,
    target bigint UNSIGNED NOT NULL,
    created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL
) $charset_collate;
SQL;

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        $this->wpdb->query( "ALTER TABLE $table_name ADD CONSTRAINT {$table_name}_unq UNIQUE (user_id,target_type,target)" );
    }

    /**
     * @return void
     */
    public function uninstall() {
        $this->wpdb->query( "DROP TABLE IF EXISTS {$this->get_follows_tablename($this->wpdb)}" );
        delete_option( $this->version_opt_name );
    }
}
