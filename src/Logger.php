<?php

namespace WPShop\WPCommunity;

class Logger {

    /**
     * @var string
     */
    protected $file = '';

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @param string $file
     */
    public function __construct( $file = '' ) {
        $this->file = $file;
    }

    /**
     * @param bool $flag
     *
     * @return bool
     */
    public function set_enabled( $flag ) {
        $this->enabled = $flag;

        return true;
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function log( $message ) {
        if ( ! $this->enabled ) {
            return;
        }

        $file_name = ( ! empty( $this->file ) ) ? $this->file : 'debug';
        $file_name .= '-' . date( 'Y-m' ) . '.log';

        $file_dir = WP_CONTENT_DIR . '/wpcommunity-logs/';

        /**
         * @since 1.0
         */
        $file_dir = apply_filters( 'wpcommunity/logger/file_dir', $file_dir );

        $file_path = $file_dir . $file_name;

        // todo вынести в настройки
        $is_debug_enabled = true;

        $date = new \DateTime( 'now', wp_timezone() );

        if ( $is_debug_enabled ) {

            if ( ! is_dir( $file_dir ) ) {
                mkdir( $file_dir, 0755, true );
                chmod( $file_dir, 0755 );

                touch( $file_dir . '/.htaccess' );
                file_put_contents( $file_dir . '/.htaccess', "Deny from all\n" );
            }

            if ( ! file_exists( $file_path ) ) {
                touch( $file_path );
                chmod( $file_path, 0644 );
            }

            $message_formatted = sprintf( "[%s] %s \r\n", $date->format( \DateTime::RFC822 ), $message );
            error_log( $message_formatted, 3, $file_path );
        }
    }

}
