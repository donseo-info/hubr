<?php

namespace WPShop\WPCommunity\Admin;

use function WPShop\WPCommunity\get_settings_url;

class Notices {

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param Settings $settings
     */
    public function __construct( Settings $settings ) {
        $this->settings = $settings;
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'admin_notices', [ $this, '_show_license_notice' ] );
        add_action( 'wp_body_open', function () {
            $this->check_is_maintainable();
        }, - 1 );
    }

    /**
     * @return void
     */
    public function _show_license_notice() {
        $screen_id = ( $screen = get_current_screen() ) ? $screen->id : null;
        if ( $this->settings->verify() ||
             'settings_page_wpcommunity-settings' === $screen_id
        ) {
            return;
        }
        ?>
        <div class="notice notice-error">
            <h2><?php echo __( 'Attention!', 'wpcommunity' ) ?></h2>
            <p>
                <?php
                printf(
                    __( 'To activate the theme you need to enter the license key on <a href="%s">this page</a>.', 'wpcommunity' ),
                    $this->get_activate_url()
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * @return string|void
     */
    protected function get_activate_url() {
        return add_query_arg( 'setup-assistant', 1, get_settings_url() );
    }

    /**
     * @return void
     * @see wp_maintenance()
     */
    protected function check_is_maintainable() {
        if ( $this->settings->verify() ) {
            return;
        }

        if ( is_user_logged_in() ) {
            $message = sprintf(
                __( 'To activate the theme you need to enter the license key on <a href="%s">this page</a>.', 'wpcommunity' ),
                $this->get_activate_url()
            );
            exit( '<p style="text-align: center;font-size:20px;">' . $message . '</p></body>' );
        }

        if ( file_exists( WP_CONTENT_DIR . '/maintenance.php' ) ) {
            require_once WP_CONTENT_DIR . '/maintenance.php';
            die();
        }

        require_once ABSPATH . WPINC . '/functions.php';
        wp_load_translations_early();

        wp_die(
            __( 'Briefly unavailable for scheduled maintenance. Check back in a minute.' ),
            __( 'Maintenance' ),
            503
        );
    }
}
