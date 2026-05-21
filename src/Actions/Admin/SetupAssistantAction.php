<?php

namespace WPShop\WPCommunity\Actions\Admin;

use JetBrains\PhpStorm\NoReturn;
use WP_Error;
use WP_Nav_Menu_Widget;
use WP_Widget;
use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\DefaultPages;
use WPShop\WPCommunity\Widgets\DarkModeSwitcherWidget;
use WPShop\WPCommunity\Widgets\MenuWidget;

class SetupAssistantAction {

    /**
     * @var DefaultPages
     */
    protected $pages;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param DefaultPages $pages
     * @param Settings     $settings
     */
    public function __construct( DefaultPages $pages, Settings $settings ) {
        $this->pages    = $pages;
        $this->settings = $settings;
    }

    /**
     * @return void
     */
    public function init() {
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_create_pages';
            add_action( "wp_ajax_{$action}", [ $this, '_create_pages' ] );
        }
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_create_menu';
            add_action( "wp_ajax_{$action}", [ $this, '_create_menu' ] );
        }
        if ( wp_doing_ajax() ) {
            $action = 'wpcommunity_create_widgets';
            add_action( "wp_ajax_{$action}", [ $this, '_create_widgets' ] );
        }
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _create_pages() {
        $created_pages = $this->pages->create_pages();

        foreach ( $created_pages as $page ) {
            if ( $this->settings->get_value( "page.{$page['name']}" ) !== $page['id'] ) {
                $this->settings->update_value( "page.{$page['name']}", $page['id'], true );
            }
        }

        wp_send_json_success( [
            'pages' => $created_pages,
        ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _create_menu() {
        $data = wp_parse_args( $_REQUEST['form'] ?? '', [
            'primary-menu'   => [],
            'sidebar-bottom' => [],
        ] );

        $menus = [
            'primary-menu'   => __( 'Primary Menu', 'wpcommunity' ),
            'sidebar-bottom' => __( 'Sidebar Bottom', 'wpcommunity' ),
        ];

        $result = [];
        foreach ( $data as $menu_location => $pages ) {

            $pages = array_keys( array_filter( $pages ) );
            if ( ! $pages ) {
                $result[] = sprintf( __( 'Unable to create empty menu in the area "%s"', 'wpcommunity' ), $menus[ $menu_location ] );
                continue; // skip empty menu
            }

            if ( wp_get_nav_menu_object( $menu_location ) ) {
                $result[] = sprintf( __( 'The menu in the "%s" area already exists', 'wpcommunity' ), $menus[ $menu_location ] );
                continue; // skip if menu exists
            }

            $menu_id = wp_create_nav_menu( $menus[ $menu_location ] );
            if ( is_wp_error( $menu_id ) ) {
                $result[] = sprintf( __( 'Unable to create menu in the area "%s": %s', 'wpcommunity' ), $menus[ $menu_location ], $menu_id->get_error_message() );
                continue;
            }

            // move menu to location
            $locations                   = get_theme_mod( 'nav_menu_locations' );
            $locations[ $menu_location ] = $menu_id;
            set_theme_mod( 'nav_menu_locations', $locations );

            // create pages if not exists
            $created_pages = $this->pages->create_pages( $pages );
            foreach ( $created_pages as $page ) {
                if ( $this->settings->get_value( "page.{$page['name']}" ) !== $page['id'] ) {
                    $this->settings->update_value( "page.{$page['name']}", $page['id'] );
                }
            }

            foreach ( $pages as $page ) {
                $page_item = $this->pages->get_page( $page );
                wp_update_nav_menu_item( $menu_id, 0, [
                    'menu-item-title'     => $page_item['title'],
                    'menu-item-type'      => 'post_type',
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => $this->settings->get_value( "page.{$page}" ),
                    'menu-item-status'    => 'publish',
                ] );
            }

            $result[] = sprintf( __( 'The menu in the "%s" area was successfully created', 'wpcommunity' ), $menus[ $menu_location ] );
        }

        wp_send_json_success( [ 'menu_items' => $result ] );
    }

    /**
     * @return void
     */
    #[NoReturn]
    public function _create_widgets() {
        $this->check_access();

        $data = wp_parse_args( $_REQUEST['form'] ?? '', [
            'sidebar-top'    => [],
            'sidebar-bottom' => [],
        ] );

        //$sidebar_widgets = get_option( 'sidebars_widgets' );
        $sidebar_widgets = retrieve_widgets();

        $widgets_factory = [
            MenuWidget::WIDGET_ID             => [
                'class'  => MenuWidget::class,
                'update' => function ( WP_Widget $widget, $params = [] ) {
                    return $widget->update( [ 'items' => $params ], [] );
                },
            ],
            DarkModeSwitcherWidget::WIDGET_ID => [
                'class' => DarkModeSwitcherWidget::class,
            ],
            'nav_menu'                        => [
                'class'  => WP_Nav_Menu_Widget::class,
                'update' => function ( WP_Widget $widget, $params = [] ) {
                    $locations = get_theme_mod( 'nav_menu_locations' );
                    if ( $locations['sidebar-bottom'] ) {
                        return [
                            'nav_menu' => $locations['sidebar-bottom'],
                            //'title'    => '',
                        ];
                    }

                    return new WP_Error( 'error', sprintf(
                        __( 'The menu of area "%s" not found', 'wpcommunity' ),
                        __( 'Sidebar Bottom', 'wpcommunity' )
                    ) );
                },
            ],
        ];

        /**
         * Detect if widget already exists in sidebar
         *
         * @param string $sidebar widget area
         * @param string $widget_id
         *
         * @return bool
         */
        $widget_exists = function ( $sidebar, $widget_id ) use ( $sidebar_widgets ) {
            foreach ( $sidebar_widgets as $_sidebar => $_widgets ) {
                if ( $_sidebar !== $sidebar ) {
                    continue;
                }
                $found_widget = array_filter( $_widgets, function ( $registered_widget_id ) use ( $widget_id ) {
                    return strpos( $registered_widget_id, $widget_id ) !== false;
                } );

                return ! empty( $found_widget );
            }

            return false;
        };

        /**
         * @param WP_Widget $widget
         * @param array     $params
         *
         * @return string
         */
        $store_widget = function ( WP_Widget $widget, $params = [] ) {
            $settings   = $widget->get_settings();
            $settings[] = $params;

            $last_key = array_keys( $settings )[ count( $settings ) - 1 ];

            $widget->save_settings( $settings );

            return "{$widget->id_base}-{$last_key}";
        };

        $to_return = [];

        $save_widgets = false;
        foreach ( $data as $sidebar => $widgets ) {
            foreach ( $widgets as $widget_id => $widget_params ) {
                $enabled = $widget_params['_enable'];
                unset( $widget_params['_enable'] );

                // skip create menu widget without pages
                if ( $widget_id === MenuWidget::WIDGET_ID ) {
                    $pages = array_keys( array_filter( $widget_params ) );
                    if ( ! $pages ) {
                        $enabled = false;
                    }
                }

                if ( ! array_key_exists( $widget_id, $widgets_factory ) ) {
                    $to_return[] = sprintf( __( 'Unable to handle widget "%s" creation', 'wpcommunity' ), $widget_id );
                    continue;
                }

                $widget_class = $widgets_factory[ $widget_id ]['class'];
                if ( class_exists( $widget_class ) ) {
                    /** @var WP_Widget $widget */
                    $widget = new $widget_class();
                } else {
                    $to_return[] = sprintf( __( 'The widget "%s" does not exist', 'wpcommunity' ), $widget_id );
                    continue;
                }

                if ( ! $enabled ) {
                    $to_return[] = sprintf( __( 'Skip creation of disabled widget "%s"', 'wpcommunity' ), $widget->name );
                    continue;
                }

                if ( $widget_exists( $sidebar, $widget_id ) ) {
                    $to_return[] = sprintf( __( 'The widget "%s" already exists', 'wpcommunity' ), $widget->name );
                    continue;
                }

                $params = [];
                if ( array_key_exists( 'update', $widgets_factory[ $widget_id ] ) ) {
                    $params = call_user_func( $widgets_factory[ $widget_id ]['update'], $widget, $widget_params );
                }

                if ( is_wp_error( $params ) ) {
                    $to_return[] = sprintf( __( 'Skip creation of widget "%s": %s', 'wpcommunity' ), $widget->name, $params->get_error_message() );
                    continue;
                }

                if ( $new_widget_row = $store_widget( $widget, $params ) ) {
                    $sidebar_widgets[ $sidebar ][] = $new_widget_row;
                    $save_widgets                  = true;

                    $to_return[] = sprintf( __( 'The widget "%s" was successfully created', 'wpcommunity' ), $widget->name );
                } else {
                    $to_return[] = sprintf( __( 'Something went wrong while creating widget "%s"', 'wpcommunity' ), $widget->name );
                }
            }
        }

        if ( $save_widgets ) {
            wp_set_sidebars_widgets( $sidebar_widgets );;
        }

        wp_send_json_success( [ 'widgets' => $to_return ] );
    }

    /**
     * @return void
     */
    protected function check_access() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( new WP_Error( 'forbidden', __( 'You are not allowed to perform this action', 'wpcommunity' ) ) );
        }
    }
}
