<?php

namespace WPShop\WPCommunity\Customizer;

use WPShop\WPCommunity\Social;
use function WPShop\WPCommunity\is_mobile;
use function WPShop\WPCommunity\is_tablet;
use function WPShop\WPCommunity\theme_container;

class CustomizerHelper {

    protected static $opt_name_stash = [];

    /**
     * @var null|array
     */
    protected static $structure_item_stash = null;

    /**
     * @param string      $name
     * @param string|null $device
     * @param array       $defaults
     *
     * @return mixed|null
     * @deprecated
     */
    public static function get_json_option( $name, $device = null, $defaults = [] ) {
        if ( null === $device ) {
            if ( is_mobile() ) {
                $device = 'mobile';
            } else if ( is_tablet() ) {
                $device = 'tablet';
            } else {
                $device = 'desktop';
            }
        }

        if ( $dot = strrpos( $name, '.', - 1 ) ) {
            $opt_name = substr( $name, 0, $dot );
        } else {
            $opt_name = $name;
        }

        $values = static::get_json_option_values( $opt_name );

        if ( is_array( $values ) && array_key_exists( $device, $values ) ) {
            if ( is_array( $values[ $device ] ) ) {
                $values = array_filter( $values[ $device ], function ( $item ) use ( $name ) {
                    return $name === $item['name'];
                } );
                if ( count( $values ) ) {
                    return wp_parse_args( current( $values ), $defaults );
                }
            }
        }

        return null;
    }

    /**
     * @param string $name
     * @param string $list
     *
     * @return bool
     */
    public static function is_in_list( $name, $list ) {
        if ( $list = static::get_json_option_values( $list ) ) {
            foreach ( $list as $device => $device_values ) {
                foreach ( $device_values as $item ) {
                    if ( $item['name'] === $name ) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $name
     * @param bool   $check_is_customize_preview
     *
     * @return bool
     */
    public static function is_item_enabled( $name, $check_is_customize_preview = true ) {
        if ( $check_is_customize_preview && is_customize_preview() ) {
            return true;
        }

        if ( null === static::$structure_item_stash ) {
            static::$structure_item_stash = [];
            foreach ( array_keys( static::get_structure_defaults() ) as $opt_name ) {
                if ( $values = static::get_json_option_values( $opt_name ) ) {
                    foreach ( $values as $device => $device_values ) {
                        foreach ( $device_values as $item ) {
                            static::$structure_item_stash[ $item['name'] ][ $device ] = $item;
                        }
                    }
                }
            }
        }

        // find in any device scope
        if ( array_key_exists( $name, static::$structure_item_stash ) ) {
            foreach ( static::$structure_item_stash[ $name ] as $device => $item ) {
                if ( $item['enabled'] ?? false ) {
                    return true;
                }
            }
        }


//        if ( $dot = strrpos( $name, '.', - 1 ) ) {
//            $opt_name = substr( $name, 0, $dot );
//        } else {
//            $opt_name = $name;
//        }
//
//        if ( $values = static::get_json_option_values( $opt_name ) ) {
//            foreach ( $values as $device => $device_values ) {
//                $device_values = array_filter( $device_values, function ( $item ) use ( $name ) {
//                    return $item['name'] === $name;
//                } );
//
//                $item = current( $device_values );
//                if ( $item && ( $item['enabled'] ?? false ) ) {
//                    return true;
//                }
//            }
//        }

        return false;
    }

    /**
     * @param string $opt_name
     *
     * @return array
     */
    public static function get_json_option_values( $opt_name ) {
        if ( array_key_exists( $opt_name, static::$opt_name_stash ) ) {
            $values = static::$opt_name_stash[ $opt_name ];
        } else if ( $values = theme_container()->get( Customizer::class )->get_option( $opt_name ) ) {
            $values = json_decode( $values, true );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                static::$opt_name_stash[ $opt_name ] = $values;
            }
        }

        return $values;
    }

    /**
     * @return array
     */
    public static function get_structure_defaults() {
        return [
            'structure.header_elements'        => json_encode( static::map_devices( [
                [
                    'name'         => 'structure.header_elements.site_branding',
                    'order'        => 10,
                    'enabled'      => true,
                    'selector'     => '.site-branding',
                    'display_prop' => 'flex',
                ],
                [
                    'name'     => 'structure.header_elements.main_navigation',
                    'order'    => 20,
                    'enabled'  => true,
                    'selector' => '.main-navigation',
                ],
                [
                    'name'         => 'structure.header_elements.social',
                    'order'        => 30,
                    'enabled'      => false,
                    'selector'     => '.header-social',
                    'display_prop' => 'flex',
                ],
                [
                    'name'         => 'structure.header_elements.search',
                    'order'        => 40,
                    'enabled'      => false,
                    'selector'     => '.site-header .search-form',
                    'display_prop' => 'flex',
                ],
                [
                    'name'     => 'structure.header_elements.html_1',
                    'order'    => 50,
                    'enabled'  => true,
                    'selector' => '.header-html-1',
                ],
                [
                    'name'     => 'structure.header_elements.html_2',
                    'order'    => 60,
                    'enabled'  => true,
                    'selector' => '.header-html-2',
                ],
            ] ) ),
            'structure.site_branding_elements' => json_encode( static::map_devices( [
                [
                    'name'     => 'structure.site_branding_elements.logo',
                    'order'    => 10,
                    'enabled'  => true,
                    'selector' => '.site-branding__logo-link, .site-branding__logo',
                ],
                [
                    'name'     => 'structure.site_branding_elements.title',
                    'order'    => 20,
                    'enabled'  => true,
                    'selector' => '.site-branding .site-title',
                ],
                [
                    'name'     => 'structure.site_branding_elements.description',
                    'order'    => 30,
                    'enabled'  => true,
                    'selector' => '.site-branding .site-description',
                ],
            ] ) ),

            'post.content_elements'      => json_encode( static::map_devices( [
                [
                    'name'     => 'post.content_elements.excerpt',
                    'order'    => 10,
                    'enabled'  => true,
                    'selector' => '.post-card__excerpt',
                ],
                [
                    'name'     => 'post.content_elements.image',
                    'order'    => 20,
                    'enabled'  => true,
                    'selector' => '.post-card__image',
                ],
                [
                    'name'     => 'post.content_elements.content',
                    'order'    => 30,
                    'enabled'  => true,
                    'selector' => '.entry-content',
                ],
            ] ) ),
            'post.header_elements'       => json_encode( static::map_devices( [
                [
                    'name'     => 'post.header_elements.author',
                    'order'    => 10,
                    'enabled'  => true,
                    'selector' => '.post-meta__author',
                ],
                [
                    'name'     => 'post.header_elements.date',
                    'order'    => 20,
                    'enabled'  => true,
                    'selector' => '.post-meta__date',
                ],
                [
                    'name'     => 'post.header_elements.category',
                    'order'    => 30,
                    'enabled'  => true,
                    'selector' => '.post-meta__category',
                ],
            ] ) ),
            'post.header_right_elements' => json_encode( static::map_devices( [
                [
                    'name'         => 'post.header_right_elements.vote',
                    'order'        => 10,
                    'enabled'      => true,
                    'selector'     => '.post-meta .vote',
                    'display_prop' => 'flex',
                ],
            ] ) ),
            'post.footer_elements'       => json_encode( static::map_devices( [
                [
                    'name'     => 'post.footer_elements.comments',
                    'order'    => 10,
                    'enabled'  => true,
                    'selector' => '.post-meta__comments',
                ],
                [
                    'name'     => 'post.footer_elements.views',
                    'order'    => 20,
                    'enabled'  => true,
                    'selector' => '.post-meta__views',
                ],
                [
                    'name'     => 'post.footer_elements.bookmarks',
                    'order'    => 30,
                    'enabled'  => true,
                    'selector' => '.post-meta__bookmarks',
                ],
                [
                    'name'     => 'post.footer_elements.tags',
                    'order'    => 40,
                    'enabled'  => true,
                    'selector' => '.post-meta__tags',
                ],
            ] ) ),
            'post.footer_right_elements' => json_encode( static::map_devices( [
                [
                    'name'     => 'post.footer_right_elements.access',
                    'order'    => 10,
                    'enabled'  => true,
                    'selector' => '.post-meta__access',
                ],
            ] ) ),

            'post_card.content_elements'      => json_encode( static::map_devices( [
                [
                    'name'     => 'post_card.content_elements.image',
                    'order'    => 20,
                    'enabled'  => true,
                    'selector' => '.post-card__image',
                ],
                [
                    'name'     => 'post_card.content_elements.excerpt',
                    'order'    => 30,
                    'enabled'  => true,
                    'selector' => '.post-card__content',
                ],
            ] ) ),
            'post_card.header_elements'       => json_encode( static::map_devices( [
                [
                    'name'     => 'post_card.header_elements.author',
                    'order'    => 10,
                    'enabled'  => true,
                    'selector' => '.post-meta__author',
                ],
                [
                    'name'     => 'post_card.header_elements.date',
                    'order'    => 20,
                    'enabled'  => true,
                    'selector' => '.post-meta__date',
                ],
                [
                    'name'     => 'post_card.header_elements.category',
                    'order'    => 30,
                    'enabled'  => true,
                    'selector' => '.post-meta__category',
                ],
            ] ) ),
            'post_card.header_right_elements' => json_encode( static::map_devices( [
                [
                    'name'         => 'post_card.header_right_elements.vote',
                    'order'        => 10,
                    'enabled'      => true,
                    'selector'     => '.post-meta .vote',
                    'display_prop' => 'flex',
                ],
            ] ) ),
            'post_card.footer_elements'       => json_encode( static::map_devices( [
                [
                    'name'     => 'post_card.footer_elements.comments',
                    'order'    => 10,
                    'enabled'  => true,
                    'selector' => '.post-meta__comments',
                ],
                [
                    'name'     => 'post_card.footer_elements.views',
                    'order'    => 20,
                    'enabled'  => true,
                    'selector' => '.post-meta__views',
                ],
                [
                    'name'     => 'post_card.footer_elements.bookmarks',
                    'order'    => 30,
                    'enabled'  => true,
                    'selector' => '.post-meta__bookmarks',
                ],
                [
                    'name'         => 'post_card.footer_elements.tags',
                    'order'        => 40,
                    'enabled'      => true,
                    'selector'     => '.post-meta__tags',
                    'display_prop' => 'flex',
                ],
            ] ) ),
            'post_card.footer_right_elements' => json_encode( static::map_devices( [
                [
                    'name'     => 'post_card.footer_right_elements.access',
                    'order'    => 10,
                    'enabled'  => true,
                    'selector' => '.post-meta__access',
                ],
            ] ) ),

            'structure.footer_elements' => json_encode( static::map_devices( [
                [
                    'name'     => 'structure.footer_elements.block_1',
                    'order'    => 10,
                    'enabled'  => true,
                    'selector' => '.site-footer__block--1',
                ],
                [
                    'name'     => 'structure.footer_elements.block_2',
                    'order'    => 20,
                    'enabled'  => false,
                    'selector' => '.site-footer__block--2',
                ],
                [
                    'name'     => 'structure.footer_elements.block_3',
                    'order'    => 30,
                    'enabled'  => false,
                    'selector' => '.site-footer__block--3',
                ],
                [
                    'name'     => 'structure.footer_elements.block_4',
                    'order'    => 40,
                    'enabled'  => false,
                    'selector' => '.site-footer__block--4',
                ],
                [
                    'name'     => 'structure.footer_elements.block_5',
                    'order'    => 50,
                    'enabled'  => false,
                    'selector' => '.site-footer__block--5',
                ],
                [
                    'name'     => 'structure.footer_elements.block_6',
                    'order'    => 60,
                    'enabled'  => false,
                    'selector' => '.site-footer__block--6',
                ],
            ] ) ),
        ];
    }

    /**
     * @return array
     */
    public static function get_default_values() {
        $social_profiles = $social_profiles_all_devices = [];
        $social_order    = 0;
        $share_services  = [];
        $share_order     = 0;
        foreach ( theme_container()->get( Social::class )->get_services() as $name => $service ) {
            if ( $service['profile'] ?? false ) {
                $social_profiles[]             = [
                    'name'         => $name,
                    'order'        => $social_order += 10,
                    'enabled'      => false,
                    'selector'     => '.social-button--' . $name,
                    'display_prop' => 'inline-flex',
                ];
                $social_profiles_all_devices[] = [
                    'name' => $name,
                    'url'  => '',
                ];
            }
            if ( $service['share'] ?? false ) {
                $share_services[] = [
                    'name'         => $name,
                    'order'        => $share_order += 10,
                    'enabled'      => false,
                    'selector'     => '.social-button--' . $name,
                    'display_prop' => 'inline-flex',
                ];
            }
        }
        //$social_profiles = array_merge( static::map_devices( $social_profiles ), static::map_devices( $social_profiles_all_devices, [ 'all_devices' ] ) );
        $social_profiles = static::map_devices( $social_profiles );

        $share_services = static::map_devices( $share_services );

        return array_merge( [
            'site_branding.logo_max_width'  => '',
            'site_branding.logo_max_height' => '2em',

            'color_scheme' => json_encode( [
                'palettes'       => ThemeColors::get_variables_for_defaults(),
                '_preview_theme' => 'dark', // for customizer
            ] ),

            'structure.html_1' => '',
            'structure.html_2' => '',

            'social_profiles.show_text'           => false,
            'social_profiles.use_original_colors' => true,
            'social_profiles.profiles'            => json_encode( $social_profiles ),

            'social_share.show_counters'       => false,
            'social_share.show_text'           => true,
            'social_share.use_original_colors' => true,
            'social_share.services'            => json_encode( $share_services ),

            'scroll_to_top.enabled'           => 0,
            'scroll_to_top.enabled_mobile',
            'scroll_to_top.background_color'  => '#ffffff',
            'scroll_to_top.use_custom_colors' => 0,
            'scroll_to_top.color'             => '#000000',
            'scroll_to_top.button_width'      => 48,
            'scroll_to_top.button_height'     => 48,
            'scroll_to_top.position'          => 'right',
            'scroll_to_top.indent_x'          => 16,
            'scroll_to_top.indent_y'          => 16,
            'scroll_to_top.icon'              => '\fe3f',
            'scroll_to_top.custom_icon'       => '',

            'toc.enable_post'     => 1,
            'toc.enable_page'     => 0,
            'toc.opened'          => 1,
            'toc.wrap_no_index'   => 0,
            'toc.prepend_content' => 1,
            'toc.title'           => __( 'Contents', 'wpcommunity' ),

            'post_card.use_full_text'  => 0,
            'post_card.excerpt_length' => 200,

            'post.hide_sidebar_1' => 0,
            'post.hide_sidebar_2' => 0,
            //'post.full_width'     => 0,

            'search.show_form'        => 1,
            'search.show_form_mobile' => 1,

            'structure.footer_elements.block_1_content' => '',
        ], static::get_structure_defaults() );
    }

    /**
     * @param array[]       $items
     * @param string[]|null $devices
     *
     * @return array
     */
    protected static function map_devices( array $items, $devices = null ) {
        $devices = $devices ?: [ 'desktop', 'tablet', 'mobile' ];

        $result = [];
        foreach ( $devices as $device ) {
            $result[ $device ] = $items;
        }

        return $result;
    }
}
