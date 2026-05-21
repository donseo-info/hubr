<?php

namespace Wpshop\Settings;

/**
 *
 * @method void render_reg_input()
 * @method void render_header( $title, $description = '', $doc_link = '' )
 * @method void render_subheader( $title, $description = '', $doc_link = '' )
 * @method void render_input( $name, $title, array $args = [], $description = '' )
 * @method void render_input_field( $name, array $args = [] )
 * @method void render_password_input( $name, $title, array $args = [], $description = '' )
 * @method void render_password_input_field( $name, array $args = [] )
 * @method void render_select( $name, $title, array $options, array $args = [], $description = '' )
 * @method void render_select_field( $name, array $options, array $args = [] )
 * @method void render_checkbox( $name, $label = '', array $args = [], $description = '' )
 * @method void render_checkbox_field( $name, $label = '', array $args = [] )
 * @method void render_textarea( $name, $title, $args = [], $description = '' )
 * @method void render_textarea_field( $name, array $args = [] )
 * @method void render_color_picker( $name, $label, array $args = [], $description = '' )
 * @method void render_color_picker_field( $name, array $args = [] )
 * @method void wrap_form( $cb )
 * @method void wp_dropdown_languages( array $args = [] )
 */
abstract class AbstractSettings {

    const VERSION = '1.4.0';

    const ASSETS_VERSION = '0.3.3';

    const TEXT_DOMAIN = '{{text-domain}}';

    /**
     * @var MaintenanceInterface
     */
    protected $maintenance;

    /**
     * @var array
     */
    protected $tabs = [];

    /**
     * @var string
     */
    protected $reg_option;

    /**
     * @var string
     */
    protected $reg_option_group;

    /**
     * @var string
     */
    protected $option;

    /**
     * @var string
     */
    protected $option_group;

    /**
     * @var string
     */
    protected $welcome_option;

    /**
     * @var string
     */
    protected $capability = 'manage_options';

    /**
     * @var array
     */
    protected $_defaults = [];

    /**
     * @var bool
     */
    protected $_defaults_init = false;

    /**
     * @var callable[]
     */
    protected $sanitizers = [];

    /**
     * @var array
     */
    protected $password_fields = [];

    /**
     * @var array|null
     */
    protected $_options;

    /**
     * @var array|null
     */
    protected $_options_with_defaults;

    /**
     * @var SettingsRenderer|null
     */
    protected $renderer;

    /**
     * @param MaintenanceInterface $maintenance
     * @param string|string[]      $reg_option ['reg-option', 'reg-option-group'] or just reg option name
     * @param string|string[]      $option     ['option', 'option-group'] or just option name, uses for store settings
     */
    public function __construct( MaintenanceInterface $maintenance, $reg_option, $option ) {
        $reg_option = is_array( $reg_option ) ? $reg_option : [ $reg_option, $reg_option . '-group' ];
        $option     = is_array( $option ) ? $option : [ $option, $option . '-group' ];

        $this->maintenance = $maintenance;
        [ $this->reg_option, $this->reg_option_group ] = $reg_option;
        [ $this->option, $this->option_group ] = $option;
        $this->welcome_option = "{$this->option}--welcome";
    }

    /**
     * @param $renderer
     *
     * @return $this
     */
    public function set_renderer( SettingsRenderer $renderer ) {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     */
    public function __call( string $name, array $arguments ) {
        if ( ! $this->renderer ) {
            $this->renderer = new SettingsRenderer( $this, static::TEXT_DOMAIN );
        }

        if ( method_exists( $this->renderer, $name ) ) {
            return call_user_func_array( [ $this->renderer, $name ], $arguments );
        }
        throw new \RuntimeException( sprintf( 'Unable to call method %s', $name ) );
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'init', function () {
            $this->verify() && $this->maintenance->init_updates( $this->get_reg_option()['license'] );
        } );

        add_action( 'init', function () {
            if ( strtoupper( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ||
                 ! current_user_can( $this->capability )
            ) {
                return;
            }

            if ( ( $_POST['option_page'] ?? '' ) === $this->reg_option_group ) {
                if ( is_multisite() && ! current_user_can( 'manage_network_options' ) ) {
                    wp_die( __( 'Sorry, you are not allowed to modify unregistered settings for this site.', static::TEXT_DOMAIN ) );
                }

                check_admin_referer( $this->reg_option_group . '-options' );

                $license = $_POST[ $this->reg_option ]['license'] ?? '';
                $result  = $this->maintenance->activate( $license, function ( $params ) {
                    $opt = wp_parse_args( $params, [
                        'license'        => '479bc2450e315315e0d94793b1c26a97',
                        'license_verify' => 'true',
                        'license_error'  => '',
                    ] );

                    update_option( $this->reg_option, $opt );
                } );

                switch ( $this->maintenance->get_type() ) {
                    case 'plugin':
                        wp_redirect( add_query_arg( 'plugin-activated', is_wp_error( $result ) ? 0 : 1, wp_get_referer() ) );
                        die;
                    case 'theme':
                        wp_redirect( add_query_arg( 'theme-activated', is_wp_error( $result ) ? 0 : 1, wp_get_referer() ) );
                        break;
                    default:
                        break;
                }

                wp_redirect( wp_get_referer() );
                die;
            }
        } );

        add_filter( 'removable_query_args', function ( $removable_query_args ) {
            $removable_query_args[] = 'plugin-activated';
            $removable_query_args[] = 'theme-activated';

            return array_unique( $removable_query_args );
        }, 11 );

        add_action( 'admin_init', function () {
            register_setting( $this->reg_option_group, $this->reg_option );

            if ( ! $this->verify() ) {
                $this->add_tab( 'dashboard-activate', __( 'Dashboard', static::TEXT_DOMAIN ) );

                return;
            } else {
                $this->add_tab( 'dashboard', __( 'Dashboard', static::TEXT_DOMAIN ) );
            }

            register_setting( $this->option_group, $this->option );

            $this->setup_tabs();
        } );

        // prepare data structure of the option
        add_action( "pre_update_option_{$this->option}", function ( $value ) {

            $options   = (array) get_option( $this->option, [] );
            $localized = $options['_localized'] ?? [];

            if ( $locale = $this->get_locale() ) {
                $localized[ $locale ] = wp_parse_args( $value, $this->get_defaults() );
                unset( $options['_localized'] );
                $value = $options;
            } else {
                $value = wp_parse_args( $value, $this->get_defaults() );
            }

            if ( $localized ) {
                $value['_localized'] = $localized;
            }

            return $value;
        } );

        add_action( 'updated_option', function ( $option ) {
            if ( $option === $this->option ) {
                $this->_options = null;
            }
        } );

        add_filter( "sanitize_option_{$this->option}", function ( $value ) {
            foreach ( $this->password_fields as $password_field ) {
                if ( array_key_exists( $password_field, $value ) && 0 === strlen( $value[ $password_field ] ) ) {
                    $value[ $password_field ] = $this->get_value( $password_field );
                }
            }

            foreach ( $this->sanitizers as $key => $fn ) {
                if ( array_key_exists( $key, $value ) && is_callable( $fn ) ) {
                    $value[ $key ] = call_user_func( $fn, $value[ $key ] );
                }
            }

            return $value;
        } );

        $action = static::ajax_actions()['hide_welcome'];
        add_action( "wp_ajax_{$action}", function () {
            update_option( $this->welcome_option, 1 );
            wp_send_json_success();
        } );

        $action = static::ajax_actions()['remove_license'];
        add_action( "wp_ajax_{$action}", function () {
            if ( ! current_user_can( 'administrator' ) ) {
                wp_send_json_error( new \WP_Error( 'remove_license', __( 'You are not allowed to remove the license', self::TEXT_DOMAIN ) ) );
            }
            delete_option( $this->reg_option );
            wp_send_json_success();
        } );
    }

    /**
     * @return string[]
     */
    public static function ajax_actions() {
        return [
            'hide_welcome'   => static::product_prefix() . 'settings_hide_welcome',
            'remove_license' => static::product_prefix() . 'settings_remove_license',
        ];
    }

    /**
     * @param string $type
     *
     * @return string
     */
    public function doc_link( $type ) {
        return '#';
    }

    /**
     * @return void
     */
    protected function setup_tabs() {

    }

    /**
     * @return bool
     */
    public function verify() {
        return true;
    }

    /**
     * @return array
     */
    public function get_reg_option() {
        return wp_parse_args( get_option( $this->reg_option, [] ), [
            'license'        => '479bc2450e315315e0d94793b1c26a97',
            'license_verify' => 'true',
            'license_error'  => '',
        ] );
    }

    /**
     * @return bool
     */
    public function do_show_welcome() {
        return ! get_option( $this->welcome_option, 0 );
    }

    /**
     * @param string $name
     * @param string $label
     *
     * @return $this
     */
    public function add_tab( $name, $label, $template_name = null ) {
        $id = 'tab-' . sanitize_html_class( $name );
        if ( null === $template_name ) {
            $template_name = $name;
        }
        $this->tabs[ $name ] = compact( 'name', 'label', 'id', 'template_name' );

        return $this;
    }

    /**
     * @return array
     */
    public function get_tabs() {
        return $this->tabs;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function get_input_name( $name ) {
        return $this->option . "[{$name}]";
    }

    /**
     * @param string $name
     *
     * @return string
     */
    public function get_reg_input_name( $name ) {
        return $this->reg_option . "[{$name}]";
    }

    /**
     * @return $this
     */
    public function register_reg_settings() {
        settings_fields( $this->reg_option_group );

        return $this;
    }

    /**
     * @return $this
     */
    public function register_settings() {
        settings_fields( $this->option_group );

        return $this;
    }

    /**
     * Example of structure of _options_with_defaults:
     * <pre>
     * $this->_options_with_defaults = [
     *     'page.join' => 1,
     *     'page.subs' => 2,
     *     '_localized' => [
     *       'ru_RU' => [
     *         'page.join' => 111,
     *         'page.subs' => 112,
     *       ],
     *     ]
     * ];
     * </pre>
     *
     * @param string      $key
     * @param bool        $null_default get null if value is same as default
     * @param string|null $locale
     *
     * @return mixed|null
     */
    public function get_value( $key, $null_default = false, $locale = null ) {
        if ( null === $locale ) {
            $locale = $this->get_locale();
        }

        if ( null === $this->_options ) {
            $this->_options               = (array) get_option( $this->option, [] );
            $this->_options_with_defaults = wp_parse_args( $this->_options, $this->get_defaults() );
        }

        if ( $null_default ) {
            // check localized first
            if ( $locale &&
                 isset( $this->_options['_localized'][ $locale ] ) &&
                 array_key_exists( $key, $this->_options['_localized'][ $locale ] ) &&
                 array_key_exists( $key, $this->get_defaults() ) &&
                 $this->_options['_localized'][ $locale ][ $key ] === $this->get_default( $key )
            ) {
                return null;
            }

            if ( array_key_exists( $key, $this->_options ) &&
                 array_key_exists( $key, $this->get_defaults() ) &&
                 $this->_options[ $key ] === $this->get_default( $key )
            ) {
                return null;
            }
        }

        if ( $locale &&
             isset( $this->_options_with_defaults['_localized'][ $locale ] ) &&
             array_key_exists( $key, $this->_options_with_defaults['_localized'][ $locale ] )
        ) {
            return $this->_options_with_defaults['_localized'][ $locale ][ $key ];
        }

        return $this->_options_with_defaults[ $key ] ?? null;
    }

    /**
     * @return string|null
     */
    public function get_locale() {
        if ( is_admin() ) {
            return $_REQUEST['locale'] ?? null;
        }

        return get_locale();
    }

    /**
     * @return bool
     */
    public function use_localized_settings() {
        /**
         * @since 1.4
         */
        $use_localized_settings = apply_filters( 'wpsc/settings/use_localized_settings', false );

        return $use_localized_settings;
    }

    /**
     * @param string $key
     *
     * @return string|null
     */
    public function get_default( $key ) {
        if ( ! $this->_defaults_init ) {
            $this->init_defaults();
            $this->_defaults_init = true;
        }

        return array_key_exists( $key, $this->_defaults ) ? $this->_defaults[ $key ] : null;
    }

    /**
     * @return array
     */
    public function get_defaults() {
        if ( ! $this->_defaults_init ) {
            $this->init_defaults();
            $this->_defaults_init = true;
        }

        return $this->_defaults;
    }

    /**
     * @return void
     */
    protected function init_defaults() {

    }

    /**
     * @return $this
     */
    public function clear_database() {
        delete_option( $this->option );
        delete_option( $this->reg_option );
        delete_option( "{$this->option}--welcome" );

        return $this;
    }

    /**
     * @return string[]
     */
    public function get_tab_icons() {
        return [
            'dashboard'          => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M273.27 7.08A24.14 24.14 0 0 0 256.09 0c-6.17-.02-12.35 2.32-17.06 7.03l-232 232c-9.37 9.37-9.37 24.57 0 33.94C11.72 277.66 17.86 280 24 280s12.28-2.34 16.97-7.03L64 249.94V464c0 8.84 7.16 16 16 16h352c8.84 0 16-7.16 16-16V250.19l22.73 22.73c4.72 4.72 10.91 7.08 17.09 7.08s12.37-2.36 17.09-7.08c9.44-9.44 9.44-24.75 0-34.19M399.99 133.81l-32-32M224 432V304h64v128h-64Zm176 0h-64V272c0-8.84-7.16-16-16-16H192c-8.84 0-16 7.16-16 16v160h-64V201.94L255.88 58.06 400 202.18v229.81Z" fill="currentColor"></path></svg>',
            'dashboard-activate' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M273.27 7.08A24.14 24.14 0 0 0 256.09 0c-6.17-.02-12.35 2.32-17.06 7.03l-232 232c-9.37 9.37-9.37 24.57 0 33.94C11.72 277.66 17.86 280 24 280s12.28-2.34 16.97-7.03L64 249.94V464c0 8.84 7.16 16 16 16h352c8.84 0 16-7.16 16-16V250.19l22.73 22.73c4.72 4.72 10.91 7.08 17.09 7.08s12.37-2.36 17.09-7.08c9.44-9.44 9.44-24.75 0-34.19M399.99 133.81l-32-32M224 432V304h64v128h-64Zm176 0h-64V272c0-8.84-7.16-16-16-16H192c-8.84 0-16 7.16-16 16v160h-64V201.94L255.88 58.06 400 202.18v229.81Z" fill="currentColor"></path></svg>',
        ];
    }

    /**
     * @param string $slug
     * @param string $name
     * @param array  $args
     *
     * @return false|void
     * @see \get_template_part()
     */
    public static function get_template_part( $slug, $name = null, $args = [] ) {
        do_action( "get_template_part_{$slug}", $slug, $name, $args );

        $templates = [];
        $name      = (string) $name;
        if ( '' !== $name ) {
            $templates[] = "{$slug}-{$name}.php";
        }

        $templates[] = "{$slug}.php";

        do_action( 'get_template_part', $slug, $name, $templates, $args );

        if ( ! static::locate_template( $templates, true, false, $args ) ) {
            return false;
        }
    }

    /**
     * @param string|array $template_names
     * @param bool         $load
     * @param bool         $require_once
     * @param array        $args
     *
     * @return string|null
     * @see \locate_template()
     */
    protected static function locate_template( $template_names, $load = false, $require_once = true, $args = [] ) {
        $located = null;
        foreach ( (array) $template_names as $template_name ) {
            if ( ! $template_name ) {
                continue;
            }

            if ( file_exists( static::get_template_parts_root() . $template_name ) ) {
                $located = static::get_template_parts_root() . $template_name;
                break;
            }
        }

        if ( ! file_exists( $located ) ) {
            trigger_error( 'Unable to locate template file ' . $template_name );

            return null;
        }


        if ( $load && '' !== $located ) {
            load_template( $located, $require_once, $args );
        }

        return $located;
    }

    /**
     * @return string
     */
    protected static function get_template_parts_root() {
        throw new \RuntimeException( __METHOD__ . " is unimplemented" );
    }

    /**
     * @return string
     */
    public static function product_prefix() {
        throw new \RuntimeException( __METHOD__ . " is unimplemented" );
    }
}
