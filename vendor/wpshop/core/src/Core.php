<?php

namespace Wpshop\Core;

/**
 * Class Core
 *
 * @version 1.1.0
 *
 * Changelog
 *
 * 1.1.0    add init_default_options()
 * 1.0.1    add minimum_php_version function
 * 1.0.0    init
 */
class Core {

    protected $theme_options;
    private $options = array();
    private $default_options = array();

    protected $_init_default_options = false;

    /**
     * Core constructor.
     *
     * @param ThemeOptions $theme_options
     */
    public function __construct( ThemeOptions $theme_options ) {
        $this->theme_options = $theme_options;
        $this->options = get_option( $this->theme_options->option_name, array() );

        add_action( 'admin_notices', array( $this, 'notice_check_license' ) );
    }


    public function get_option_name() {
        return $this->theme_options->option_name;
    }


    public function notice_check_license() {

        if ( ! $this->is_check_license() ) {
            echo '<div class="notice notice-error">';
            echo '<h2>' . __( 'Attention!', $this->theme_options->text_domain ) . '</h2>';
            echo '<p>' . sprintf( __( 'To activate theme you need to enter the license key on <a href="%s">this page</a>.', $this->theme_options->text_domain ), admin_url( 'options-general.php?page=theme_settings' ) ) . '</p>';
            echo '</div>';
        }

    }


    public function is_check_license() {
		return true;

    }


    public function check_license() {

        if ( ! $this->is_check_license() ) {
            exit( '<p style="text-align: center;font-size:20px;">' . __( 'You need to activate the license in Settings section of Admin panel', $this->theme_options->text_domain ) . '</p>' );
        }

    }


    /**
     * Get all options
     *
     * @return array|mixed
     */
    public function get_options() {
        return $this->options;
    }


    /**
     * Get option
     *
     * @param $option
     *
     * @return mixed|string
     */
    public function get_option( $option ) {
        $this->init_default_options();

        // для кастомайзера всегда свежее значение
        if ( is_customize_preview() ) {
            $this->options = wp_parse_args(
                get_option( $this->theme_options->option_name, array() ),
                $this->get_default_options()
            );
        }
        return ( isset( $this->options[ $option ] ) ) ? $this->options[ $option ] : '' ;
    }

    /**
     * @return void
     */
    protected function init_default_options() {
        if ( ! $this->_init_default_options ) {
            $this->set_default_options( apply_filters( 'wpshop_core_default_options', [] ) );
            $this->_init_default_options = true;
        }
    }

    /**
     * Set option
     *
     * @param $option_name
     * @param $option_value
     *
     * @return bool False if value was not updated and true if value was updated.
     */
    public function set_option( $option_name, $option_value ) {
        $options                 = $this->get_options();
        $options[ $option_name ] = $option_value;
        $this->options           = $options;

        return update_option( $this->theme_options->option_name, $options );
    }

    /**
     * @param array $options_array
     *
     * @return bool False if value was not updated and true if value was updated.
     */
    public function update_options( array $options_array ) {
        $options = $this->get_options();
        foreach ( $options_array as $key => $value ) {
            $options[ $key ] = $value;
        }
        $this->options = $options;

        return update_option( $this->theme_options->option_name, $options );
    }


    /**
     * Display option
     *
     * @param $option
     */
    public function the_option( $option ) {
        $the_option = $this->get_option( $option );
        if ( ! is_array( $the_option ) ) {
            echo $the_option;
        }
    }


    /**
     * Set default options
     *
     * @param array $default_options
     */
    public function set_default_options( $default_options = array() ) {
        $this->default_options = $default_options;

        $this->options = wp_parse_args(
            $this->options,
            $default_options
        );

        $this->_init_default_options = true;
    }

    /**
     * @return array
     */
    public function get_default_options() {
        $this->init_default_options();

        return $this->default_options;
    }


    public function get_default_option( $option ) {
        return ( isset( $this->default_options[ $option ] ) ) ? $this->default_options[ $option ] : '' ;
    }


    /**
     * Required PHP minimum version
     *
     * @param   $php_version    string  For example 5.3.0
     */
    public function minimum_php_version( $php_version ) {
        if ( version_compare( PHP_VERSION, $php_version, '<' ) ) {
            add_action( 'after_setup_theme', function() use ( $php_version ) {
                $message  = '<h1>' . sprintf( __( 'Theme %s requires PHP version >= %s', $this->theme_options->text_domain ), $this->theme_options->theme_name, $php_version ) . '</h1>';
                $message .= '<p>' . sprintf( __( 'The version of PHP installed on your hosting is outdated.', $this->theme_options->text_domain ), PHP_VERSION ) . '</p>';
                $message .= '<p>' . sprintf( __( 'Now you use PHP: %s. It can be unsafe and slowly your site. Please upgrade PHP.', $this->theme_options->text_domain ), PHP_VERSION ) . '</p>';
                $message .= '<p>' . __( 'You can change PHP version by yourself or ask about it your hosting provider.', $this->theme_options->text_domain ) . '</p>';
                wp_die( $message );
            } );
        }
    }


    /**
     * Is show elements on page
     *
     * @param string $element
     * @param int $post
     *
     * @return bool
     */
    public function is_show_element( $element = '', $post = 0 ) {
        $post = get_post( $post );

        if ( ! is_singular() ) {
            return true;
        }

        if ( empty( $post->ID ) )
            return false;

        if ( empty( $element ) )
            return false;

        $hide_elements = get_post_meta( $post->ID, 'hide_elements', true );
        if ( empty( $hide_elements ) ) $hide_elements = array();

        return ! in_array( $element, (array) $hide_elements );
    }
}
