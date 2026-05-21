<?php

namespace WPShop\WPCommunity\Customizer;

use WP_Customize_Manager;
use WPShop\WPCommunity\Customizer\Control\ColorSchemeControl;
use WPShop\WPCommunity\Customizer\Control\SocialProfilesControl;
use WPShop\WPCommunity\Customizer\Control\SortableCheckboxesControl;
use WPShop\WPCommunity\Social;
use function WPShop\WPCommunity\get_device_edges;
use function WPShop\WPCommunity\theme_container;

/**
 * @see https://developer.wordpress.org/themes/customize-api/customizer-objects/
 */
class Customizer {

    /**
     * @var string
     */
    protected $capability = 'edit_theme_options';

    /**
     * @var string
     */
    protected $style_id = 'wpcommunity-customize-css';

    /**
     * @var string
     */
    protected $option_namespace;

    /**
     * @var array|null
     */
    protected $defaults;

    /**
     * @param string $option_namespace
     */
    public function __construct( $option_namespace ) {
        $this->option_namespace = $option_namespace;
    }

    /**
     * @return array|mixed
     */
    public function get_defaults( $key = null ) {
        if ( ! $this->defaults ) {
            $this->defaults = CustomizerHelper::get_default_values();
        }

        if ( $key ) {
            return $this->defaults[ $key ] ?? null;
        }

        return $this->defaults;
    }

    /**
     * @param string $key
     *
     * @return mixed
     * @see Customizer::$defaults
     */
    public function get_option( $key ) {
        $option = get_option( $this->option_namespace );

        /**
         * Allows to change value of customize option after retrieving
         *
         * @since 1.0
         */
        $option = (array) apply_filters( 'wpcommunity/customizer/option', $option, $key );

        return array_key_exists( $key, $option ) ? $option[ $key ] : $this->get_defaults( $key );
    }


    /**
     * @return void
     */
    public function init() {
        add_action( 'wp_head', [ $this, '_output_variables' ], 100 );
        add_action( 'customize_controls_print_styles', [ $this, '_customize_controls_print_styles' ] );
        add_action( 'customize_preview_init', [ $this, '_preview_init' ] );
        add_action( 'customize_register', [ $this, '_register' ] );

        //add_filter( 'wpcommunity/layout/content_full_width', [ $this, '_set_full_width' ], 10, 2 );

        add_action( 'wp_head', function () {

            if ( is_customize_preview() ) {
                ?>
                <script id="wpcommunity-init-theme">
                    document.documentElement.setAttribute("theme", localStorage.getItem("theme-preview") ? localStorage.getItem("theme-preview") === 'dark' ? 'dark' : 'light' : window.matchMedia("(prefers-color-scheme: dark)").matches ? 'dark' : 'light');
                </script>
                <?php
            } else {

                /**
                 * Allows to set default color scheme
                 *
                 * [ru] Позволяет установить цветовую схему по умолчанию
                 *
                 * @since 1.1
                 */
                $default_theme = apply_filters( 'wpcommunity/appearance/default_theme', null );
                if ( ! in_array( $default_theme, [ 'dark', 'light', null ], true ) ) {
                    $default_theme = null;
                }

                if ( $default_theme ) :
                    ?>
                    <script id="wpcommunity-init-theme">
                        document.documentElement.setAttribute(
                            "theme",
                            localStorage.getItem("theme") ?
                                (localStorage.getItem("theme") === 'dark' ? 'dark' : 'light') :
                                '<?php echo $default_theme ?>'
                        );
                    </script>
                <?php
                else:
                    ?>
                    <script id="wpcommunity-init-theme">
                        document.documentElement.setAttribute(
                            "theme", localStorage.getItem("theme") ?
                                (localStorage.getItem("theme") === 'dark' ? 'dark' : 'light') :
                                (window.matchMedia("(prefers-color-scheme: dark)").matches ? 'dark' : 'light')
                        );
                    </script>
                <?php
                endif;

            }
        } );
    }

    /**
     * @param bool  $result
     * @param array $classes
     *
     * @return bool
     */
    public function _set_full_width( $result, $classes ) {
        if ( is_singular( 'post' ) && $this->get_option( 'post.full_width' ) ) {
            if ( in_array( 'no-sidebar', $classes ) ||
                 in_array( 'sidebar-1', $classes )
            ) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * @return void
     */
    public function _customize_controls_print_styles() {
        $edges = get_device_edges();

        /**
         * Allows to change width of preview window for mobile in customizer
         *
         * @since 1.0
         */
        $mobile_preview_width = apply_filters( 'wpcommunity/customizer/mobile_preview_width', 320 );

        /**
         * Allows to change width of preview window for tablet in customizer
         *
         * @since 1.0
         */
        $tablet_preview_width = apply_filters( 'wpcommunity/customizer/tablet_preview_width', $edges['tablet'] );
        ?>
        <style id="wpsc-customize-core-styles">
            .preview-mobile .wp-full-overlay-main {

                width: <?php echo $mobile_preview_width ?>px;
            }

            .preview-tablet .wp-full-overlay-main {
                width: <?php echo $tablet_preview_width ?>px;
            }
        </style>
        <?php
    }


    /**
     * @return void
     */
    public function _output_variables() {
        $css = new CssBuilder();

        if ( $scheme = $this->get_option( 'color_scheme' ) ) {
            $scheme = json_decode( $scheme, true );
            if ( json_last_error() == JSON_ERROR_NONE ) {
                foreach ( $scheme['palettes'] as $theme => $colors ) {
                    $rule = $css->new_rule( 'html[theme="' . $theme . '"]:root' );
                    foreach ( $colors as $variable => $value ) {
                        $rule->add_property( "--{$variable}", $value );
                    }
                }
            }
        }

//        $colors = $this->get_option( 'color_scheme.light' );
//        $colors = json_decode( $colors, true );
//        if ( json_last_error() == JSON_ERROR_NONE ) {
//            $rule = $css->new_rule( 'html[theme=light]:root' );
//            foreach ( $colors as $type => $value ) {
//                $rule->add_property( "--{$type}", $value );
//            }
//        }
//
//        $colors = $this->get_option( 'color_scheme.dark' );
//        $colors = json_decode( $colors, true );
//        if ( json_last_error() == JSON_ERROR_NONE ) {
//            $rule = $css->new_rule( 'html[theme=dark]:root' );
//            foreach ( $colors as $type => $value ) {
//                $rule->add_property( "--{$type}", $value );
//            }
//        }


        $css->pretty = defined( 'WP_DEBUG' ) && WP_DEBUG;
        ?>
        <style id="<?php echo $this->style_id ?>"><?php echo $css ?></style>
        <?php
    }

    /**
     * @return void
     */
    public function _preview_init() {
        wp_enqueue_script(
            'wpcommunity-customizer',
            get_template_directory_uri() . '/assets/admin/js/wpcommunity-customizer.min.js',
            [ 'customize-preview', 'jquery' ],
            false,
            true
        );
        wp_localize_script( 'wpcommunity-customizer', 'wpcommunity_customizer_options', [
            'option_namespace' => $this->option_namespace,
            'style_selector'   => '#' . $this->style_id,
        ] );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     *
     * @return void
     */
    public function _register( WP_Customize_Manager $wp_customize ) {
        $wp_customize->register_control_type( ColorSchemeControl::class );;
        $wp_customize->register_control_type( SortableCheckboxesControl::class );;
        $wp_customize->register_control_type( SocialProfilesControl::class );;

//        $wp_customize->add_panel( 'color_scheme', [
//            'title'       => __( 'Color Scheme', 'wpcommunity' ),
//            'description' => '',
//            'priority'    => 70,
//        ] );
//
//        $this->configure_colors( $wp_customize, 'color_scheme' );

        $wp_customize->add_panel( 'structure', [
            'title'       => __( 'Structure', 'wpcommunity' ),
            'description' => '',
            'priority'    => 80,
        ] );

        $this->configure_site_branding( $wp_customize );
        $this->configure_structure_header_section( $wp_customize, 'structure' );
        $this->configure_structure_footer_section( $wp_customize, 'structure' );
        $this->configure_post_card_section( $wp_customize, 'structure' );
        $this->configure_post_section( $wp_customize, 'structure' );
        $this->configure_search_section( $wp_customize, 'structure' );

        $wp_customize->add_panel( 'modules', [
            'title'    => __( 'Modules', 'wpcommunity' ),
            'priority' => 90,
        ] );

        /**
         * Modules
         */
        $this->configure_social_profiles( $wp_customize, 'modules', 10 );
        $this->configure_social_share( $wp_customize, 'modules', 20 );
        $this->configure_scroll_to_top( $wp_customize, 'modules', 30 );
        $this->configure_table_of_contents( $wp_customize, 'modules', 40 );

        $wp_customize->add_section( 'wpcommunity_color_scheme', [
            'title'      => __( 'Color Scheme', 'wpcommunity' ),
            'capability' => $this->capability,
            'priority'   => 90,
        ] );

        $this->add_setting( $wp_customize, 'color_scheme', [
            //'sanitize_callback' => 'sanitize_hex_color',
            'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new ColorSchemeControl( $wp_customize, $this->get_setting_name( 'color_scheme' ), [
            'label'    => __( 'Light Theme', 'wpcommunity' ),
            'priority' => 10,
            'section'  => 'wpcommunity_color_scheme',
            'theme'    => 'light',
        ] ) );

        $wp_customize->add_setting( 'custom_logo_dark', [
            'theme_supports' => [ 'custom-logo' ],
            'transport'      => 'postMessage',
        ] );
        $custom_logo_args = get_theme_support( 'custom-logo' );
        $wp_customize->add_control( new \WP_Customize_Cropped_Image_Control( $wp_customize, 'custom_logo_dark', [
            'label'         => __( 'Logo Dark', 'wpcommunity' ),
            'description'   => __( 'Logotype for dark theme', 'wpcommunity' ),
            'section'       => 'title_tagline',
            'priority'      => 9,
            'height'        => $custom_logo_args[0]['height'] ?? null,
            'width'         => $custom_logo_args[0]['width'] ?? null,
            'flex_height'   => $custom_logo_args[0]['flex-height'] ?? null,
            'flex_width'    => $custom_logo_args[0]['flex-width'] ?? null,
            'button_labels' => [
                'select'       => __( 'Select logo' ),
                'change'       => __( 'Change logo' ),
                'remove'       => __( 'Remove' ),
                'default'      => __( 'Default' ),
                'placeholder'  => __( 'No logo selected' ),
                'frame_title'  => __( 'Select logo' ),
                'frame_button' => __( 'Choose logo' ),
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'homepage_h1' );
        $this->add_control( $wp_customize, 'homepage_h1', [
            'label'       => __( 'Homepage h1', 'wpcommunity' ),
            'description' => __( 'The title to be displayed on the home page if no site name is set', 'wpcommunity' ),
            'type'        => 'text',
            'section'     => 'title_tagline',
            'priority'    => 9,
        ] );

    }

    protected function get_setting_name( $name ) {
        return "{$this->option_namespace}[{$name}]";
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     *
     * @return void
     */
    protected function configure_colors( $wp_customize, $panel = '' ) {
        foreach (
            [
                'dark'  => __( 'Dark', 'wpcommunity' ),
                'light' => __( 'Light', 'wpcommunity' ),
            ] as $scheme_type => $scheme_label
        ) {
            $wp_customize->add_section( $scheme_type, [
                'title'      => $scheme_label,
                'capability' => $this->capability,
                'priority'   => 70,
                'panel'      => $panel,
            ] );

            $this->add_setting( $wp_customize, "color_scheme.{$scheme_type}.color_white_bg", [
                'sanitize_callback' => 'sanitize_hex_color',
            ] );
            $this->add_control( $wp_customize, "color_scheme.{$scheme_type}.color_white_bg", [
                'label'    => __( 'White Background', 'wpcommunity' ),
                'type'     => 'color',
                'priority' => 10,
                'section'  => $scheme_type,
            ] );

            $this->add_setting( $wp_customize, "color_scheme.{$scheme_type}.color_body_bg", [
                'sanitize_callback' => 'sanitize_hex_color',
            ] );
            $this->add_control( $wp_customize, "color_scheme.{$scheme_type}.color_body_bg", [
                'label'    => __( 'Body Background', 'wpcommunity' ),
                'type'     => 'color',
                'priority' => 10,
                'section'  => $scheme_type,
            ] );

            $this->add_setting( $wp_customize, "color_scheme.{$scheme_type}.color_bg_light", [
                'sanitize_callback' => 'sanitize_hex_color',
            ] );
            $this->add_control( $wp_customize, "color_scheme.{$scheme_type}.color_bg_light", [
                'label'    => __( 'Background Light', 'wpcommunity' ),
                'type'     => 'color',
                'priority' => 10,
                'section'  => $scheme_type,
            ] );
        }
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     *
     * @return void
     */
    public function configure_site_branding( $wp_customize ) {
        $sanitize_width = function ( $value ) {
            $chars  = str_split( trim( $value ) );
            $number = [];
            $unit   = [];

            $gather_numeric = true;
            foreach ( $chars as $char ) {
                if ( $gather_numeric ) {
                    if ( is_numeric( $char ) ) {
                        $number[] = $char;
                    } else {
                        $gather_numeric = false;

                        $unit[] = $char;
                    }
                } else {
                    // skip if found numeric?
                    $unit[] = $char;
                }
            }

            $number = implode( '', $number );
            $unit   = implode( '', $unit );

            if ( ! in_array( $unit, [
                'cm',
                'mm',
                'Q',
                'in',
                'pc',
                'pt',
                'px',
                'em',
                'ex',
                'ch',
                'rem',
                'lh',
                'vw',
                'vh',
                'vmin',
                'vmax',
            ] ) ) {
                $unit = '';
            }

            return $number . trim( $unit );
        };

        $this->add_setting( $wp_customize, 'site_branding.logo_max_width', [
            'sanitize_callback' => $sanitize_width,
        ] );
        $wp_customize->add_control( $this->get_setting_name( 'site_branding.logo_max_width' ), [
            'label'    => __( 'Logo Max Width', 'wpcommunity' ),
            'priority' => 50,
            'section'  => 'title_tagline',
        ] );

        $this->add_setting( $wp_customize, 'site_branding.logo_max_height', [
            'sanitize_callback' => $sanitize_width,
        ] );
        $wp_customize->add_control( $this->get_setting_name( 'site_branding.logo_max_height' ), [
            'label'    => __( 'Logo Max Height', 'wpcommunity' ),
            'priority' => 50,
            'section'  => 'title_tagline',
        ] );

//        $this->add_setting( $wp_customize, 'site_branding.hide_site_name', [] );
//        $wp_customize->add_control( $this->get_setting_name( 'site_branding.hide_site_name' ), [
//            'label'    => __( 'Hide Site Name', 'wpcommunity' ),
//            'priority' => 52,
//            'section'  => 'title_tagline',
//            'type'     => 'checkbox',
//        ] );
//        $this->add_setting( $wp_customize, 'site_branding.hide_site_description', [] );
//        $wp_customize->add_control( $this->get_setting_name( 'site_branding.hide_site_description' ), [
//            'label'    => __( 'Hide Site Description', 'wpcommunity' ),
//            'priority' => 53,
//            'section'  => 'title_tagline',
//            'type'     => 'checkbox',
//        ] );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     * @param string               $panel
     *
     * @return void
     */
    protected function configure_structure_header_section( $wp_customize, $panel ) {
        $wp_customize->add_section( 'header', [
            'title'       => __( 'Header', 'wpcommunity' ),
            'description' => __( 'Here you can customize the appearance of site header', 'wpcommunity' ),
            'panel'       => $panel,
            'priority'    => 10,
            'capability'  => $this->capability,
        ] );

        $this->add_setting( $wp_customize, 'structure.header_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'structure.header_elements' ), [
            'label'     => __( 'Header Elements', 'wpcommunity' ),
            'list_name' => 'structure_header_elements',
            'priority'  => 10,
            'section'   => 'header',
            'items'     => [
                'structure.header_elements.site_branding'   => [
                    'label' => __( 'Site Branding', 'wpcommunity' ),
                    //'_editable' => false,
                    //'_sortable' => false,
                ],
                'structure.header_elements.main_navigation' => [
                    'label' => __( 'Main Navigation', 'wpcommunity' ),
                ],
                'structure.header_elements.social'          => [
                    'label' => __( 'Social', 'wpcommunity' ),
                ],
                'structure.header_elements.search'          => [
                    'label' => __( 'Search', 'wpcommunity' ),
                ],
                'structure.header_elements.html_1'          => [
                    'label' => __( 'HTML 1', 'wpcommunity' ),
                ],
                'structure.header_elements.html_2'          => [
                    'label' => __( 'HTML 2', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'structure.site_branding_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'structure.site_branding_elements' ), [
            'label'     => __( 'Site Branding Elements', 'wpcommunity' ),
            'list_name' => 'structure_site_branding_elements',
            'priority'  => 10,
            'section'   => 'header',
            'items'     => [
                'structure.site_branding_elements.logo'        => [
                    'label' => __( 'Logo', 'wpcommunity' ),
                ],
                'structure.site_branding_elements.title'       => [
                    'label' => __( 'Title', 'wpcommunity' ),
                ],
                'structure.site_branding_elements.description' => [
                    'label' => __( 'Description', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'structure.html_1', [
        ] );
        $this->add_control( $wp_customize, 'structure.html_1', [
            'label'    => __( 'HTML 1', 'wpcommunity' ),
            'type'     => 'textarea',
            'priority' => 20,
            'section'  => 'header',
        ] );
        $this->add_setting( $wp_customize, 'structure.html_2', [
        ] );
        $this->add_control( $wp_customize, 'structure.html_2', [
            'label'    => __( 'HTML 2', 'wpcommunity' ),
            'type'     => 'textarea',
            'priority' => 30,
            'section'  => 'header',
        ] );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     * @param string               $panel
     *
     * @return void
     */
    protected function configure_structure_footer_section( $wp_customize, $panel ) {
        $section = 'footer';
        $wp_customize->add_section( $section, [
            'title'       => __( 'Footer', 'wpcommunity' ),
            'description' => __( 'Here you can customize the appearance of site footer', 'wpcommunity' ),
            'panel'       => $panel,
            'priority'    => 30,
            'capability'  => $this->capability,
        ] );

        $this->add_setting( $wp_customize, 'structure.footer_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'structure.footer_elements' ), [
            'label'     => __( 'Footer Elements', 'wpcommunity' ),
            'list_name' => 'structure_footer_elements',
            'priority'  => 10,
            'section'   => $section,
            'items'     => [
                'structure.footer_elements.block_1' => [
                    'label' => sprintf( __( 'Block %d', 'wpcommunity' ), 1 ),
                    //'_editable' => false,
                    //'_sortable' => false,
                ],
                'structure.footer_elements.block_2' => [
                    'label' => sprintf( __( 'Block %d', 'wpcommunity' ), 2 ),
                    //'_editable' => false,
                    //'_sortable' => false,
                ],
                'structure.footer_elements.block_3' => [
                    'label' => sprintf( __( 'Block %d', 'wpcommunity' ), 3 ),
                    //'_editable' => false,
                    //'_sortable' => false,
                ],
                'structure.footer_elements.block_4' => [
                    'label' => sprintf( __( 'Block %d', 'wpcommunity' ), 4 ),
                    //'_editable' => false,
                    //'_sortable' => false,
                ],
                'structure.footer_elements.block_5' => [
                    'label' => sprintf( __( 'Block %d', 'wpcommunity' ), 5 ),
                    //'_editable' => false,
                    //'_sortable' => false,
                ],
                'structure.footer_elements.block_6' => [
                    'label' => sprintf( __( 'Block %d', 'wpcommunity' ), 6 ),
                    //'_editable' => false,
                    //'_sortable' => false,
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'structure.footer_elements.block_1_content', [
        ] );
        $this->add_control( $wp_customize, 'structure.footer_elements.block_1_content', [
            'label'    => __( 'Block 1 HTML / Widgets', 'wpcommunity' ),
            'type'     => 'textarea',
            'priority' => 10,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'structure.footer_elements.block_2_content', [
        ] );
        $this->add_control( $wp_customize, 'structure.footer_elements.block_2_content', [
            'label'    => sprintf( __( 'Content of Block %d', 'wpcommunity' ), 2 ),
            'type'     => 'textarea',
            'priority' => 20,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'structure.footer_elements.block_3_content', [
        ] );
        $this->add_control( $wp_customize, 'structure.footer_elements.block_3_content', [
            'label'    => sprintf( __( 'Content of Block %d', 'wpcommunity' ), 3 ),
            'type'     => 'textarea',
            'priority' => 30,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'structure.footer_elements.block_4_content', [
        ] );
        $this->add_control( $wp_customize, 'structure.footer_elements.block_4_content', [
            'label'    => sprintf( __( 'Content of Block %d', 'wpcommunity' ), 4 ),
            'type'     => 'textarea',
            'priority' => 40,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'structure.footer_elements.block_5_content', [
        ] );
        $this->add_control( $wp_customize, 'structure.footer_elements.block_5_content', [
            'label'    => sprintf( __( 'Content of Block %d', 'wpcommunity' ), 5 ),
            'type'     => 'textarea',
            'priority' => 50,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'structure.footer_elements.block_6_content', [
        ] );
        $this->add_control( $wp_customize, 'structure.footer_elements.block_6_content', [
            'label'    => sprintf( __( 'Content of Block %d', 'wpcommunity' ), 6 ),
            'type'     => 'textarea',
            'priority' => 60,
            'section'  => $section,
        ] );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     * @param string               $panel
     *
     * @return void
     */
    public function configure_post_section( $wp_customize, $panel = '' ) {
        $section = 'post';
        $wp_customize->add_section( $section, [
            'title'       => __( 'Post', 'wpcommunity' ),
            'description' => __( 'Here you can customize the appearance of the post content', 'wpcommunity' ),
            'panel'       => $panel,
            'priority'    => 30,
            'capability'  => $this->capability,
        ] );

        $this->add_setting( $wp_customize, 'post.content_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post.content_elements' ), [
            'label'     => __( 'Content Elements', 'wpcommunity' ),
            'list_name' => 'post_content_elements',
            'priority'  => 10,
            'section'   => $section,
            'items'     => [
                'post.content_elements.excerpt' => [
                    'label' => __( 'Excerpt', 'wpcommunity' ),
                ],
                'post.content_elements.image'   => [
                    'label' => __( 'Image', 'wpcommunity' ),
                ],
                'post.content_elements.content' => [
                    'label'     => __( 'Content', 'wpcommunity' ),
                    '_editable' => false,
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'post.header_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post.header_elements' ), [
            'label'           => __( 'Header Elements', 'wpcommunity' ),
            'list_name'       => 'post_header_elements',
            'connect_with'    => 'post_header_right_elements,post_footer_elements,post_footer_right_elements',
            'exclude_connect' => [ 'rights' ],
            'priority'        => 20,
            'section'         => $section,
            'items'           => [
                'post.header_elements.author'   => [
                    'label' => __( 'Author', 'wpcommunity' ),
                ],
                'post.header_elements.date'     => [
                    'label' => __( 'Date', 'wpcommunity' ),
                ],
                'post.header_elements.category' => [
                    'label' => __( 'Category', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'post.header_right_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post.header_right_elements' ), [
            'label'        => '* ' . __( 'Header Right Block', 'wpcommunity' ),
            'list_name'    => 'post_header_right_elements',
            'connect_with' => 'post_header_elements,post_footer_elements,post_footer_right_elements',
            'priority'     => 20,
            'section'      => $section,
            'items'        => [
                'post.header_right_elements.vote' => [
                    'label' => __( 'Vote', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'post.footer_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post.footer_elements' ), [
            'label'           => __( 'Footer Elements', 'wpcommunity' ),
            'list_name'       => 'post_footer_elements',
            'connect_with'    => 'post_header_elements,post_header_right_elements,post_footer_right_elements',
            'exclude_connect' => [ 'rights' ],
            'priority'        => 30,
            'section'         => $section,
            'items'           => [
                'post.footer_elements.comments'  => [
                    'label' => __( 'Comments', 'wpcommunity' ),
                ],
                'post.footer_elements.views'     => [
                    'label' => __( 'Views', 'wpcommunity' ),
                ],
                'post.footer_elements.bookmarks' => [
                    'label' => __( 'Bookmark', 'wpcommunity' ),
                ],
                'post.footer_elements.tags'      => [
                    'label' => __( 'Tags', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'post.footer_right_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post.footer_right_elements' ), [
            'label'        => '* ' . __( 'Footer Right Block', 'wpcommunity' ),
            'list_name'    => 'post_footer_right_elements',
            'connect_with' => 'post_header_elements,post_header_right_elements,post_footer_elements',
            'priority'     => 30,
            'section'      => $section,
            'items'        => [
                'post.footer_right_elements.access' => [
                    'label' => __( 'Access', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'post.hide_sidebar_1' );
        $wp_customize->add_control( $this->get_setting_name( 'post.hide_sidebar_1' ), [
            'type'     => 'checkbox',
            'label'    => __( 'Hide Sidebar 1', 'wpcommunity' ),
            'priority' => 40,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'post.hide_sidebar_2' );
        $wp_customize->add_control( $this->get_setting_name( 'post.hide_sidebar_2' ), [
            'type'     => 'checkbox',
            'label'    => __( 'Hide Sidebar 2', 'wpcommunity' ),
            'priority' => 50,
            'section'  => $section,
        ] );

//        $this->add_setting( $wp_customize, 'post.full_width' );
//        $wp_customize->add_control( $this->get_setting_name( 'post.full_width' ), [
//            'type'        => 'checkbox',
//            'label'       => __( 'Use Full Width', 'wpcommunity' ),
//            'description' => __( 'If sidebars are disabled or only one is set, the full width of the content area will be used', 'wpcommunity' ),
//            'priority'    => 60,
//            'section'     => $section,
//        ] );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     * @param string               $panel
     *
     * @return void
     */
    protected function configure_post_card_section( $wp_customize, $panel = '' ) {
        $section = 'post_card';
        $wp_customize->add_section( $section, [
            'title'       => __( 'Post Card', 'wpcommunity' ),
            'description' => __( 'Here you can customize the appearance of the post card content', 'wpcommunity' ),
            'panel'       => $panel,
            'priority'    => 30,
            'capability'  => $this->capability,
        ] );

        $this->add_setting( $wp_customize, 'post_card.content_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post_card.content_elements' ), [
            'label'     => __( 'Content Elements', 'wpcommunity' ),
            'list_name' => 'post_card_content_elements',
            'priority'  => 10,
            'section'   => $section,
            'items'     => [
                'post_card.content_elements.image'   => [
                    'label' => __( 'Image', 'wpcommunity' ),
                ],
                'post_card.content_elements.excerpt' => [
                    'label' => __( 'Excerpt', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'post_card.header_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post_card.header_elements' ), [
            'label'           => __( 'Header Elements', 'wpcommunity' ),
            'list_name'       => 'post_card_header_elements',
            'connect_with'    => 'post_card_header_right_elements,post_card_footer_elements,post_card_footer_right_elements',
            'exclude_connect' => [ 'rights' ],
            'priority'        => 20,
            'section'         => $section,
            'items'           => [
                'post_card.header_elements.author'   => [
                    'label' => __( 'Author', 'wpcommunity' ),
                ],
                'post_card.header_elements.date'     => [
                    'label' => __( 'Date', 'wpcommunity' ),
                ],
                'post_card.header_elements.category' => [
                    'label' => __( 'Category', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'post_card.header_right_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post_card.header_right_elements' ), [
            'label'        => '* ' . __( 'Header Right Block', 'wpcommunity' ),
            'list_name'    => 'post_card_header_right_elements',
            'connect_with' => 'post_card_header_elements,post_card_footer_elements,post_card_footer_right_elements',
            'priority'     => 20,
            'section'      => $section,
            'items'        => [
                'post_card.header_right_elements.vote' => [
                    'label' => __( 'Vote', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'post_card.footer_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post_card.footer_elements' ), [
            'label'           => __( 'Footer Elements', 'wpcommunity' ),
            'list_name'       => 'post_card_footer_elements',
            'connect_with'    => 'post_card_header_elements,post_card_header_right_elements,post_card_footer_right_elements',
            'exclude_connect' => [ 'rights' ],
            'priority'        => 30,
            'section'         => $section,
            'items'           => [
                'post_card.footer_elements.comments'  => [
                    'label' => __( 'Comments', 'wpcommunity' ),
                ],
                'post_card.footer_elements.views'     => [
                    'label' => __( 'Views', 'wpcommunity' ),
                ],
                'post_card.footer_elements.bookmarks' => [
                    'label' => __( 'Bookmark', 'wpcommunity' ),
                ],
                'post_card.footer_elements.tags'      => [
                    'label' => __( 'Tags', 'wpcommunity' ),
                ],
            ],
        ] ) );

        $this->add_setting( $wp_customize, 'post_card.footer_right_elements', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'post_card.footer_right_elements' ), [
            'label'        => '* ' . __( 'Footer Right Block', 'wpcommunity' ),
            'list_name'    => 'post_card_footer_right_elements',
            'connect_with' => 'post_card_header_elements,post_card_header_right_elements,post_card_footer_elements',
            'priority'     => 30,
            'section'      => $section,
            'items'        => [
                'post_card.footer_right_elements.access' => [
                    'label' => __( 'Access', 'wpcommunity' ),
                ],
            ],
        ] ) );

        // не используется, сейчас для отдельных типов лент эта опции включаются в настройках темы
//        $this->add_setting( $wp_customize, 'post_card.use_full_text' );
//        $wp_customize->add_control( $this->get_setting_name( 'post_card.use_full_text' ), [
//            'type'        => 'checkbox',
//            'label'       => __( 'Use Full Content of the Post', 'wpcommunity' ),
//            'description' => __( 'If enabled, the full content of the record will be displayed instead of the excerpt', 'wpcommunity' ),
//            'priority'    => 40,
//            'section'     => $section,
//        ] );

        $this->add_setting( $wp_customize, 'post_card.excerpt_length' );
        $wp_customize->add_control( $this->get_setting_name( 'post_card.excerpt_length' ), [
            'type'            => 'number',
            'label'           => __( 'Excerpt Length', 'wpcommunity' ),
            'description'     => __( 'The option specifies the number of words to be displayed in the excerpt', 'wpcommunity' ),
            'priority'        => 50,
            'section'         => $section,
            'active_callback' => function () {
                return ! $this->get_option( 'post_card.use_full_text' );
            },
        ] );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     * @param string               $panel
     *
     * @return void
     */
    protected function configure_search_section( $wp_customize, $panel ) {
        $section = 'search';
        $wp_customize->add_section( $section, [
            'title'       => __( 'Search', 'wpcommunity' ),
            'description' => __( 'Here you can customize the appearance of the search page', 'wpcommunity' ),
            'panel'       => $panel,
            'priority'    => 50,
            'capability'  => $this->capability,
        ] );

        $this->add_setting( $wp_customize, 'search.show_form' );
        $wp_customize->add_control( $this->get_setting_name( 'search.show_form' ), [
            'type'     => 'checkbox',
            'label'    => __( 'Show Form', 'wpcommunity' ),
            'priority' => 10,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'search.show_form_mobile' );
        $wp_customize->add_control( $this->get_setting_name( 'search.show_form_mobile' ), [
            'type'     => 'checkbox',
            'label'    => __( 'Show Form on Mobile', 'wpcommunity' ),
            'priority' => 20,
            'section'  => $section,
        ] );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     * @param string               $panel
     * @param int                  $panel_priority
     *
     * @return void
     */
    protected function configure_social_profiles( $wp_customize, $panel, $panel_priority ) {
        $section = 'social_profiles';
        $wp_customize->add_section( $section, [
            'title'      => __( 'Social Profiles', 'wpcommunity' ),
            'panel'      => $panel,
            'priority'   => $panel_priority,
            'capability' => $this->capability,
        ] );

        $this->add_setting( $wp_customize, 'social_profiles.show_text', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'social_profiles.show_text', [
            'label'    => __( 'Show Label', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 20,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'social_profiles.use_original_colors', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'social_profiles.use_original_colors', [
            'label'    => __( 'Use Original Colors', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 30,
            'section'  => $section,
        ] );

        $profiles = theme_container()->get( Social::class )->get_services();
        $profiles = array_filter( $profiles, function ( $item ) {
            return $item['profile'] ?? false;
        } );
        $profiles = array_map( function ( $service ) {
            return [ 'label' => $service['label'] ];
        }, $profiles );

        $this->add_setting( $wp_customize, 'social_profiles.profiles', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SocialProfilesControl( $wp_customize, $this->get_setting_name( 'social_profiles.profiles' ), [
            'label'    => __( 'Profiles', 'wpcommunity' ),
            'priority' => 40,
            'section'  => $section,
            'items'    => $profiles,
        ] ) );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     * @param string               $panel
     * @param int                  $panel_priority
     *
     * @return void
     */
    protected function configure_social_share( $wp_customize, $panel, $panel_priority ) {
        $section = 'social_share';
        $wp_customize->add_section( $section, [
            'title'      => __( 'Social Share', 'wpcommunity' ),
            'panel'      => $panel,
            'priority'   => $panel_priority,
            'capability' => $this->capability,
        ] );

        $this->add_setting( $wp_customize, 'social_share.show_counters', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'social_share.show_counters', [
            'label'    => __( 'Show Counters', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 20,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'social_share.show_text', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'social_share.show_text', [
            'label'    => __( 'Show Label', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 30,
            'section'  => $section,
        ] );
        $this->add_setting( $wp_customize, 'social_share.use_original_colors', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'social_share.use_original_colors', [
            'label'    => __( 'Use Original Colors', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 40,
            'section'  => $section,
        ] );

        $profiles = theme_container()->get( Social::class )->get_services();
        $profiles = array_map( function ( $service ) {
            return [ 'label' => $service['label'] ];
        }, $profiles );

        $this->add_setting( $wp_customize, 'social_share.services', [
            //'transport' => 'postMessage',
        ] );
        $wp_customize->add_control( new SortableCheckboxesControl( $wp_customize, $this->get_setting_name( 'social_share.services' ), [
            'label'    => __( 'Services', 'wpcommunity' ),
            'priority' => 50,
            'section'  => $section,
            'items'    => $profiles,
        ] ) );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     * @param string               $panel
     * @param int                  $panel_priority
     *
     * @return void
     */
    public function configure_scroll_to_top( $wp_customize, $panel, $panel_priority ) {
        $section = 'scroll_to_top';
        $wp_customize->add_section( $section, [
            'title'      => __( 'Scroll to Top Button', 'wpcommunity' ),
            'panel'      => $panel,
            'priority'   => $panel_priority,
            'capability' => $this->capability,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.enabled', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'scroll_to_top.enabled', [
            'label'    => __( 'Enable Scroll to Top', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 10,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.enabled_mobile', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'scroll_to_top.enabled_mobile', [
            'label'    => __( 'Enable on Mobile', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 20,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.use_custom_colors', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'scroll_to_top.use_custom_colors', [
            'label'    => __( 'Use Custom Colors', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 30,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.background_color', );
        $this->add_control( $wp_customize, 'scroll_to_top.background_color', [
            'label'           => __( 'Background Color', 'wpcommunity' ),
            'type'            => 'color',
            'sanitize'        => 'sanitize_hex_color',
            'priority'        => 40,
            'section'         => $section,
            'active_callback' => function () {
                return $this->get_option( 'scroll_to_top.use_custom_colors' );
            },
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.color' );
        $this->add_control( $wp_customize, 'scroll_to_top.color', [
            'label'           => __( 'Arrow Color', 'wpcommunity' ),
            'type'            => 'color',
            'sanitize'        => 'sanitize_hex_color',
            'priority'        => 50,
            'section'         => $section,
            'active_callback' => function () {
                return $this->get_option( 'scroll_to_top.use_custom_colors' );
            },
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.button_width', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'scroll_to_top.button_width', [
            'label'       => __( 'Button Width', 'wpcommunity' ),
            'description' => __( 'The value will be applied in pixels', 'wpcommunity' ),
            'type'        => 'range',
            'sanitize'    => 'integer',
            'input_attrs' => [ 'min' => 24, 'max' => 200, 'step' => 1 ],
            'priority'    => 60,
            'section'     => $section,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.button_height', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'scroll_to_top.button_height', [
            'label'       => __( 'Button Height', 'wpcommunity' ),
            'description' => __( 'The value will be applied in pixels', 'wpcommunity' ),
            'type'        => 'range',
            'sanitize'    => 'integer',
            'input_attrs' => [ 'min' => 20, 'max' => 200, 'step' => 1 ],
            'priority'    => 70,
            'section'     => $section,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.position' );
        $this->add_control( $wp_customize, 'scroll_to_top.position', [
            'label'    => __( 'Position', 'wpcommunity' ),
            'type'     => 'select',
            'sanitize' => function ( $value ) {
                return in_array( $value, [ 'left', 'right' ], true ) ? $value : 'right';
            },
            'choices'  => [
                'left'  => __( 'Left', 'wpcommunity' ),
                'right' => __( 'Right', 'wpcommunity' ),
            ],
            'priority' => 80,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.indent_x', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'scroll_to_top.indent_x', [
            'label'       => __( 'Indentation in x-axis', 'wpcommunity' ),
            'description' => __( 'The value will be applied in pixels', 'wpcommunity' ),
            'type'        => 'range',
            'sanitize'    => 'integer',
            'input_attrs' => [ 'min' => 4, 'max' => 80, 'step' => 1 ],
            'priority'    => 90,
            'section'     => $section,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.indent_y', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'scroll_to_top.indent_y', [
            'label'       => __( 'Indentation in y-axis', 'wpcommunity' ),
            'description' => __( 'The value will be applied in pixels', 'wpcommunity' ),
            'type'        => 'range',
            'sanitize'    => 'integer',
            'input_attrs' => [ 'min' => 4, 'max' => 80, 'step' => 1 ],
            'priority'    => 100,
            'section'     => $section,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.icon' );
        $this->add_control( $wp_customize, 'scroll_to_top.icon', [
            'label'    => __( 'Icon', 'wpcommunity' ),
            'type'     => 'select',
            'choices'  => [
                ''      => __( 'Custom Icon', 'wpcommunity' ),
                '\fe3f' => '︿',
                '\fe3d' => '︽',
                '\2191' => '↑',
                '\21d1' => '⇑',
                '\2924' => '⤤',
            ],
            'priority' => 110,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'scroll_to_top.custom_icon' );
        $this->add_control( $wp_customize, 'scroll_to_top.custom_icon', [
            'label'           => __( 'Custom Icon', 'wpcommunity' ),
            'description'     => esc_html( __( 'You can specify <img> or <svg> content', 'wpcommunity' ) ),
            'type'            => 'textarea',
            'priority'        => 120,
            'section'         => $section,
            'active_callback' => function () {
                return ! $this->get_option( 'scroll_to_top.icon' );
            },
        ] );
    }

    /**
     * @param WP_Customize_Manager $wp_customize
     * @param string               $panel
     * @param int                  $panel_priority
     *
     * @return void
     */
    public function configure_table_of_contents( $wp_customize, $panel, $panel_priority ) {
        $section = 'table_of_contents';
        $wp_customize->add_section( $section, [
            'title'      => __( 'Table of Contents', 'wpcommunity' ),
            'panel'      => $panel,
            'priority'   => $panel_priority,
            'capability' => $this->capability,
        ] );

        $this->add_setting( $wp_customize, 'toc.enable_post', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'toc.enable_post', [
            'label'    => __( 'Enable on Posts', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 10,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'toc.enable_page', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'toc.enable_page', [
            'label'    => __( 'Enable on Pages', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 20,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'toc.opened', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'toc.opened', [
            'label'    => __( 'Default Opened', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 30,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'toc.wrap_no_index', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'toc.wrap_no_index', [
            'label'    => __( 'Wrap in Noindex', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 40,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'toc.prepend_content', [
            'sanitize_callback' => 'absint',
        ] );
        $this->add_control( $wp_customize, 'toc.prepend_content', [
            'label'    => __( 'Output the Content at the Beginning of the Post', 'wpcommunity' ),
            'type'     => 'checkbox',
            'priority' => 50,
            'section'  => $section,
        ] );

        $this->add_setting( $wp_customize, 'toc.title' );
        $this->add_control( $wp_customize, 'toc.title', [
            'label'    => __( 'Contents Header', 'wpcommunity' ),
            'type'     => 'text',
            'priority' => 60,
            'section'  => $section,
        ] );
    }

    /**
     * Helper for the WP_Customize_Manager::add_setting()
     *
     * @param WP_Customize_Manager $wp_customize
     * @param string               $name
     * @param array                $args
     *
     * @return void
     * @see WP_Customize_Manager::add_setting()
     */
    public function add_setting( WP_Customize_Manager $wp_customize, $name, $args = [] ) {
        $args = wp_parse_args( $args, [
            'type'       => 'option',
            'transport'  => 'refresh',
            'capability' => $this->capability,
            'default'    => $this->get_defaults( $name ),
        ] );
        $wp_customize->add_setting( $this->get_setting_name( $name ), $args );
    }

    /**
     * Helper for the WP_Customize_Manager::add_control()
     *
     * @param WP_Customize_Manager $wp_customize
     * @param string               $name
     * @param array                $args
     *
     * @return void
     * @see WP_Customize_Manager::add_control()
     */
    public function add_control( WP_Customize_Manager $wp_customize, $name, $args ) {
        $wp_customize->add_control( $this->get_setting_name( $name ), $args );
    }
}
