<?php

namespace Wpshop\Core;

/**
 * Class ThemeSettings
 *
 * 1.0.0    2020-09-10    Edit link to documentation
 * 1.1.0    2020-12-24    Add fallback url for license check, add check 200 status code
 * 1.1.1    2021-01-28    Use THEME_ORIG_VERSION if exists in api params
 */
class ThemeSettings {

    protected $options;

    /**
     * ThemeSettings constructor.
     *
     * @param ThemeOptions $options
     */
    public function __construct( ThemeOptions $options ) {

        $this->options = $options;

        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'init_settings' ) );

        add_action( 'wp_ajax_wpshop_reset_settings', array( $this, 'ajax_reset_settings' ) );

    }


    public function add_admin_menu() {
        add_options_page(
            esc_html__( $this->options->settings_page_title, $this->options->text_domain ),
            esc_html__( $this->options->settings_menu_title, $this->options->text_domain ),
            'manage_options',
            $this->options->settings_menu_slug,
            array( $this, 'page_layout' )
        );
    }


    public function init_settings() {
        register_setting(
            'settings_group',
            $this->options->settings_name,
            array( $this, 'sanitize_callback' )
        );

        add_settings_section(
            'site_settings_section',
            '',
            '',
            'site_settings'
        );

        add_settings_field(

        );

        add_settings_field(
            $this->options->settings_name . '_settings',
            __( 'Settings', $this->options->text_domain ),
            array( $this, 'render_settings_field' ),
            'site_settings',
            'site_settings_section'
        );
    }


    public function page_layout() {
        // Check required user capability
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', $this->options->text_domain ) );
        }

        // Admin Page Layout
        echo '<div class="wrap">' . PHP_EOL;
        echo '	<h1>' . get_admin_page_title() . '</h1>' . PHP_EOL;
        echo '<div style="background: #fff;padding: 10px 20px;border: 2px solid #4057a3;margin: 10px 0;">';
        echo '  <h2>' . __( 'You can familiarize yourself with the documentation on the topic', $this->options->text_domain ) . ' <a href="https://support111.wpshop1111.ru/docs/themes/' . $this->options->theme_name . '/" target="_blank">' . __( 'by this link', $this->options->text_domain ) . '</a>.</h2>';
        echo '  <p>' . __( 'Appearance and color settings are in the customizer', $this->options->text_domain ) . ' <strong>' . __( 'Appearance> Settings', $this->options->text_domain ) . '</strong>.</p>';
        echo '</div>';
        echo '	<form action="options.php" method="post">' . PHP_EOL;

        settings_fields( 'settings_group' );
        do_settings_sections( 'site_settings' );
        submit_button();

        echo '	</form>' . PHP_EOL;
        echo '</div>' . PHP_EOL;
    }


    public function render_license_field() {

		return null;
    }


    public function render_settings_field() {

        $nonce = wp_create_nonce( 'wpshop_reset_settings' );

        $export = get_option( $this->options->option_name );
        if ( ! empty( $export ) ) {
            $export = base64_encode( json_encode( $export ) );
        }

        echo '<div class="wpshop-export-settings">';
        echo '<label>' . __( 'Export settings', $this->options->text_domain ) . ':</label>';
        echo '<textarea class="large-text code" onmouseover="this.select()">' . $export . '</textarea>';
        echo '<p class="description">' . __( 'Copy this code to any text file to save all settings of this site', $this->options->text_domain ) . '</p>';
        echo '</div>';

        echo '<div class="wpshop-import-settings">';
        echo '<label>' . __( 'Import settings', $this->options->text_domain ) . ':</label>';
        echo '<textarea name="import_settings" class="large-text code"></textarea>';
        echo '<p class="description">' . __( 'Danger! Old settings will be removed before import! Paste code to this field and press Save', $this->options->text_domain ) . '</p>';
        echo '</div>';

        echo '<div class="wpshop-reset-settings">';
        echo '<button class="button wpshop-button-danger js-wpshop-reset-settings" data-nonce="'. $nonce .'">' . __( 'Reset all settings', $this->options->text_domain ) . '</button>';
        echo '<p class="description">' . __( 'Danger! Reset all customizer settings. Reset counters, styles, sidebar settings etc.', $this->options->text_domain ) . '</p>';
        echo '</div>';

    }

    /**
     * @param array $options
     *
     * @return false|mixed|void
     */
    public function sanitize_callback( $options ) {
        return true;

    }

    /**
     * @param string $license
     *
     * @return array|\WP_Error|null
     */
    protected function get_success_response( $license ) {


        return null;
    }


    function ajax_reset_settings() {

        check_ajax_referer( 'wpshop_reset_settings' );

        if ( delete_option( $this->options->option_name ) ) {
            _e( 'Successfully deleted', $this->options->text_domain );
        } else {
            _e( 'We cant delete settings', $this->options->text_domain );
        }

        wp_die();
    }
}
