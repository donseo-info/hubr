<?php

namespace WPShop\WPCommunity;

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Features\GoogleReCaptcha;

class AssetProvider {

    /**
     * @return void
     */
    public function init() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'admin_enqueue_scripts' ] );
//        add_action( 'customize_preview_init', [ $this, 'customize_preview_init' ] );
    }

    /**
     * @return void
     */
    public function enqueue_scripts() {
        wp_register_style( 'wpcommunity-editor',
            get_template_directory_uri() . '/assets/public/css/editor.min.css',
            [ 'wpcommunity-style' ],
            THEME_ORIGINAL_VERSION
        );

        wp_enqueue_style(
            'wpcommunity-style',
            get_template_directory_uri() . '/assets/public/css/style.min.css',
            [],
            THEME_ORIGINAL_VERSION
        );

        if ( is_archive() ) {
            wp_add_inline_style( 'wpcommunity-style', ':root {--breadcrumbs-width: 900px}' );
        }

        wp_add_inline_style( 'wpcommunity-style', '.post-card__image{text-align:center;}' );

        wp_add_inline_style( 'wpcommunity-style', '
.wp-video{max-width:320px!important;margin:20px auto!important;}
.post-gallery{position:relative;overflow:hidden;border-radius:8px;margin-bottom:20px;}
.post-gallery__track{display:flex;transition:transform .35s ease;}
.post-gallery__slide{min-width:100%;flex-shrink:0;}
.post-gallery__img{width:100%;height:auto;display:block;max-height:400px;object-fit:contain;margin:0 auto;}
.post-gallery__btn{position:absolute;top:50%;transform:translateY(-50%);background:rgba(0,0,0,.45);color:#fff;border:none;font-size:32px;line-height:1;padding:8px 14px;cursor:pointer;border-radius:4px;z-index:10;transition:background .15s;}
.post-gallery__btn:hover{background:rgba(0,0,0,.7);}
.post-gallery__btn--prev{left:10px;}
.post-gallery__btn--next{right:10px;}
.post-gallery__dots{position:absolute;bottom:10px;left:50%;transform:translateX(-50%);display:flex;gap:6px;}
.post-gallery__dot{width:8px;height:8px;border-radius:50%;background:rgba(255,255,255,.5);cursor:pointer;transition:background .2s;}
.post-gallery__dot.active{background:#fff;}
' );

        wp_enqueue_script( 'hubr-gallery', get_template_directory_uri() . '/assets/public/js/gallery.js', [], '1.0', true );



        wp_register_script(
            'wpcommunity-g-recaptcha',
            'https://www.google.com/recaptcha/api.js?render=' . get_setting( 'grecaptcha.site_key' ),
            [],
            null,
            true
        );

        $scripts_deps = [ 'jquery' ];

        /**
         * Hook for preparing the dependencies of the main theme script
         *
         * [ru] Хук для подготовки зависимостей основного скрипта темы
         *
         * @hooked \WPShop\WPCommunity\Features\GoogleReCaptcha::_add_script_deps()
         *
         * @since 1.0
         */
        $scripts_deps = apply_filters( 'wpcommunity/assets/script_deps', $scripts_deps );

        wp_enqueue_script(
            'wpcommunity-scripts',
            get_template_directory_uri() . '/assets/public/js/scripts.min.js',
            $scripts_deps,
            THEME_ORIGINAL_VERSION,
            true
        );

        /**
         * Hook for preparing parameters of the main theme script
         *
         * [ru] Хук для подготовки параметров основного скрипта темы
         *
         * @since 1.0
         */
        $script_globals = apply_filters( 'wpcommunity/assets/script_globals', [
            'url'      => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'wpcommunity-nonce' ),
            'site_url' => site_url(),
            'i18n'     => [
                'saved'   => __( 'Saved', 'wpcommunity' ),
                'confirm' => __( 'Are you sure?', 'wpcommunity' ),
            ],

            'template_directory_uri' => get_template_directory_uri(),

            'svg_sprite_version' => '1.1',
        ] );
        wp_localize_script( 'wpcommunity-scripts', 'wpsc_globals', $script_globals );

        if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
            wp_enqueue_script( 'comment-reply' );
        }

        wp_register_script(
            'wpcommunity-goodshare',
            get_template_directory_uri() . '/assets/public/js/inc/goodshare.min.js', // //cdn.jsdelivr.net/npm/goodshare.js@6/goodshare.min.js
            [],
            false,
            true
        );


        do_action( 'wpcommunity/assets/enqueue_scripts' );

//        wp_localize_script( 'wpbox-scripts-general', 'wpbox_globals', [
//            'theme'   => THEME_SLUG,
//            'version' => THEME_ORIGINAL_VERSION,
//        ] );

        // remove /wp-includes/css/dist/block-library/theme.min.css
//	    wp_dequeue_style( 'wp-block-library-theme' );
    }

    /**
     * @return void
     */
    public function admin_enqueue_scripts() {

        $scripts_suffix = wp_scripts_get_suffix();

        wp_register_style( 'wpcommunity-modal', get_template_directory_uri() . '/assets/admin/css/modal.min.css' );

        wp_enqueue_script(
            'wpcommunity-admin-scripts',
            get_template_directory_uri() . '/assets/admin/js/scripts.min.js',
            [ 'jquery' ],
            THEME_ORIGINAL_VERSION,
            true
        );

        wp_localize_script( 'wpcommunity-admin-scripts', 'wpsc_globals', [
            'url'   => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'wpcommunity-nonce' ),
            'i18n'  => [
                'confirm'       => __( 'Are you sure?', 'wpcommunity' ),
                'select_status' => __( 'Please, select order status', 'wpcommunity' ),
            ],
        ] );


        wp_register_style( 'wpcommunity-theme-settings', get_template_directory_uri() . '/assets/admin/css/theme-settings.min.css', [
            'wp-color-picker',
        ], \Wpshop\Settings\AbstractSettings::ASSETS_VERSION . '-0.1' );

        if ( get_current_screen() && in_array( get_current_screen()->id, [
                'settings_page_wpcommunity-settings',
            ] ) ) {
            wp_enqueue_script( 'wp-color-picker' );
            wp_enqueue_style( 'wp-color-picker' );

            wp_enqueue_code_editor( [ 'type' => 'text/html' ] );

            wp_enqueue_style( 'wpcommunity-theme-settings' );
            wp_enqueue_script(
                'wpcommunity-theme-settings',
                get_template_directory_uri() . '/assets/admin/js/theme-settings.min.js',
                [
                    'jquery',
                    'wp-theme-plugin-editor',
                ],
                \Wpshop\Settings\AbstractSettings::ASSETS_VERSION . '-0.1',
                true
            );
            wp_localize_script( 'wpcommunity-theme-settings', 'wpcommunity_settings_globals', [
                'storage_key' => 'wpcommunity-theme-tab',
                'actions'     => Settings::ajax_actions(),
                'i18n'        => [
                    'edit'    => __( 'edit', 'wpcommunity' ),
                    'created' => _x( 'created', 'page', 'wpcommunity' ),
                ],
            ] );
        }

        if ( get_current_screen() && in_array( get_current_screen()->id, [
                'order',
            ] )
        ) {
            wp_enqueue_style(
                'wpcommunity-style',
                get_template_directory_uri() . '/assets/admin/css/order.min.css',
                [],
                THEME_ORIGINAL_VERSION
            );
        }

        // widgets
        if ( get_current_screen() && get_current_screen()->id === 'widgets' ) {
            wp_enqueue_style( 'wpcommunity-widget-menu', get_template_directory_uri() . '/assets/admin/css/widget-menu.min.css' );
            wp_enqueue_script(
                'wpcommunity-widget-menu',
                get_template_directory_uri() . '/assets/admin/js/widget-menu.min.js',
                [ 'jquery' ]
            );
        }
        if ( get_current_screen() && in_array( get_current_screen()->id, [ 'user-edit', 'profile' ] ) ) {
            wp_enqueue_script(
                'wpcommunity-user-edit',
                get_template_directory_uri() . '/assets/admin/js/user-edit.min.js',
                [ 'jquery' ]
            );
            wp_localize_script( 'wpcommunity-user-edit', 'wpcommunity_user_edit_globals', [
                'i18n' => [
                    'more' => __( 'show more', 'wpcommunity' ),
                ],
            ] );
        }

        wp_register_script(
            'wpcommunity-customizer-utils',
            get_template_directory_uri() . "/assets/admin/js/customizer/customizer-utils{$scripts_suffix}.js",
            [ 'jquery', 'customize-controls' ],
            '1.0',
            true
        );

        wp_register_style(
            'wpcommunity-control-devices',
            get_template_directory_uri() . "/assets/admin/css/customizer/control-devices.min.css",
            [],
            '1.0'
        );

        // menu icon select
//        if ( get_current_screen() && get_current_screen()->id === 'nav-menus' ) {
//            wp_enqueue_style(
//                'wpcommunity-menu-icons',
//                get_template_directory_uri() . '/assets/admin/css/menu-icons.min.css',
//                [ 'wpcommunity-modal' ]
//            );
//            wp_enqueue_script(
//                'wpcommunity-menu-icons',
//                get_template_directory_uri() . '/assets/admin/js/menu-icons.min.js',
//                [ 'jquery' ]
//            );
//        }

        if ( get_current_screen() && get_current_screen()->id === 'appearance_page_wpcommunity-ad' ) {
            wp_enqueue_style( 'wpcommunity-theme-settings' );

            wp_enqueue_style(
                'wpcommunity-advertisement',
                get_template_directory_uri() . '/assets/admin/css/advertisement.min.css',
                [ 'wpcommunity-theme-settings' ],
                '1.0'
            );

            wp_enqueue_script(
                'wpcommunity-advertisement',
                get_template_directory_uri() . '/assets/admin/js/advertisement.min.js',
                [
                    'jquery',
                    'wp-theme-plugin-editor',
                ],
                '1.0',
                true
            );

            wp_enqueue_code_editor( [ 'type' => 'text/html' ] );
        }
    }
}
