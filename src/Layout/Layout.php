<?php

namespace WPShop\WPCommunity\Layout;

use WPShop\WPCommunity\Context;
use WPShop\WPCommunity\Customizer\CssBuilder;
use WPShop\WPCommunity\Customizer\CssRule;
use WPShop\WPCommunity\Customizer\Customizer;
use WPShop\WPCommunity\Customizer\CustomizerHelper;
use WPShop\WPCommunity\Social;
use function WPShop\WPCommunity\get_device_edges;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\get_share_buttons;
use function WPShop\WPCommunity\is_post_element_hidden;
use function WPShop\WPCommunity\is_sidebar_hidden;
use function WPShop\WPCommunity\theme_container;

class Layout {

    /**
     * @var Layout
     */
    protected $customizer;

    /**
     * @var SinglePost
     */
    protected $single_post;

    /**
     * @var PostCard
     */
    protected $post_card;

    /**
     * @var array
     */
    protected $device_media = [
        'mobile'  => null,
        'tablet'  => '(min-width:768px)',
        'desktop' => '(min-width:1024px)',
    ];

    /**
     * @param Customizer $customizer
     */
    public function __construct(
        Customizer $customizer,
        SinglePost $single_post,
        PostCard $post_card
    ) {
        $this->customizer  = $customizer;
        $this->single_post = $single_post;
        $this->post_card   = $post_card;

        $device_edges       = get_device_edges();
        $this->device_media = [
            'mobile'  => null,
            'tablet'  => "(min-width:{$device_edges['tablet']}px)",
            'desktop' => "(min-width:{$device_edges['desktop']}px)",
        ];
    }

    /**
     * @return void
     */
    public function init() {
        add_action( 'wp_head', [ $this, '_output_styles' ], 110 );

        add_action( 'after_setup_theme', [ $this, '_set_content_width' ], 0 );

        add_action( 'wpcommunity/posts/loop', [ $this, '_main_loop' ] );
        add_action( 'wpcommunity/posts/loop', [ $this, '_the_posts_pagination' ], 15 );
        add_filter( 'the_posts_pagination_args', [ $this, '_set_post_navigation_args' ] );

//        add_action( 'wpcommunity/article/content', [ $this, '_output_header' ] );
//        add_action( 'wpcommunity/article/content', [ $this, '_output_thumbnail' ] );

        add_action( 'wpcommunity/header/inner', [ $this, '_output_header_site_branding' ] );
        add_action( 'wpcommunity/header/inner', [ $this, '_output_header_navigation' ], 20 );
        add_action( 'wpcommunity/header/inner', [ $this, '_output_header_search' ], 30 );
        add_action( 'wpcommunity/header/inner', [ $this, '_output_header_social' ], 40 );
        add_action( 'wpcommunity/header/inner', [ $this, '_output_header_html_1' ], 50 );
        add_action( 'wpcommunity/header/inner', [ $this, '_output_header_html_2' ], 60 );

        add_action( 'wpcommunity/comments/output', [ $this, '_output_social_share' ] );

        add_action( 'wpcommunity/footer/footer', [ $this, '_output_footer_blocks' ] );

        add_filter( 'wpcommunity/footer-block/content', 'do_shortcode' );

        add_filter( 'body_class', [ $this, '_set_body_classes' ] );
        add_filter( 'body_class', [ $this, '_set_sidebar_body_classes' ] );

        $this->single_post->init_actions();
        $this->post_card->init_actions();

        // todo move to dedicated class
        /**
         * Allows to change menu expand icon
         *
         * @since 1.0
         */
        $expand_icon = apply_filters(
            'wpcommunity/navigation/expand_icon',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 14.542 5.846 8.32a1.073 1.073 0 0 0-1.53 0 1.102 1.102 0 0 0 0 1.546l6.614 6.686c.59.597 1.55.597 2.14 0l6.613-6.686a1.102 1.102 0 0 0 0-1.546 1.073 1.073 0 0 0-1.529 0L12 14.542Z" fill="currentColor"></path></svg>',
        );
        add_filter( 'nav_menu_item_title', function ( $item_output, $menu_item ) use ( $expand_icon ) {
            if ( property_exists( $menu_item, 'classes' ) &&
                 is_array( $menu_item->classes ) &&
                 in_array( 'menu-item-has-children', $menu_item->classes )
            ) {
                $item_output .= '<span class="menu-expand-icon js-menu-expand-icon" data-display="block">' . $expand_icon . '</span>';
            }

            return $item_output;
        }, 20, 2 );

        add_filter( 'wp_nav_menu', function ( $nav_menu ) {
            preg_match_all( '/<li(.+?)class="(.+?)current-menu-item(.+?)>(<a(.+?)>(.+?)<\/a>)/ui', $nav_menu, $matches );

            if ( isset( $matches[4] ) && ! empty( $matches[4] ) && preg_match( '/<a/ui', $matches[4][0] ) ) {
                foreach ( $matches[4] as $k => $v ) {
                    $classes = 'removed-link';
                    if ( apply_filters( 'wpcommunity/navigation/preserve_removed_link_classes', false ) ) {
                        $classes = trim( $matches[2][ $k ] ) . ' ' . $classes;
                    }
                    if ( ! is_paged() ) {
                        $nav_menu = str_replace( $v, '<span class="' . $classes . '">' . $matches[6][ $k ] . '</span>', $nav_menu );
                    }
                }
            }

            return $nav_menu;
        }, PHP_INT_MAX );

        add_filter( 'wp_nav_menu_objects', function ( $sorted_menu_items ) {
            array_walk( $sorted_menu_items, function ( $item ) {
                $item->classes[] = 'menu-item--text-nowrap';
            } );

            return $sorted_menu_items;
        } );

        // отключаем style="width: 1280px" у изображений с подписью
        add_filter( 'img_caption_shortcode_width', '__return_empty_string' );

        add_filter( 'wpcommunity/layout/header_html_1_content', 'do_shortcode' );
        add_filter( 'wpcommunity/layout/header_html_1_content', 'trim' );
        add_filter( 'wpcommunity/layout/header_html_2_content', 'do_shortcode' );
        add_filter( 'wpcommunity/layout/header_html_2_content', 'trim' );

        add_action( 'wp_footer', function () {
            if ( $this->customizer->get_option( 'scroll_to_top.enabled' ) ||
                 $this->customizer->get_option( 'scroll_to_top.enabled_mobile' )
            ) {
                get_template_part( 'template-parts/elements/scroll-to-top' );
            }
        } );
    }

    /**
     * @return void
     */
    public function _set_content_width() {

        /**
         * Set the content width in pixels, based on the theme's design and stylesheet.
         *
         * [ru] Установите ширину содержимого в пикселях, основываясь на дизайне и стилях темы.
         *
         * @since 1.0
         */
        $width = apply_filters( 'wpcommunity/content/width', 640 );

        $GLOBALS['content_width'] = $width;
    }

    /**
     * @return void
     */
    public function _output_header_site_branding() {
        if ( CustomizerHelper::is_item_enabled( 'structure.header_elements.site_branding' ) ) {
            get_template_part( 'template-parts/elements/header/site-branding' );
        }
    }

    /**
     * @return void
     */
    public function _output_header_navigation() {
        if ( CustomizerHelper::is_item_enabled( 'structure.header_elements.main_navigation' ) ) {
            get_template_part( 'template-parts/elements/header/navigation' );
        }
    }

    /**
     * @return void
     */
    public function _output_header_search() {
        if ( CustomizerHelper::is_item_enabled( 'structure.header_elements.search' ) ) {
            get_search_form();
        }
    }

    /**
     * @return void
     */
    public function _output_header_social() {
        if ( ! CustomizerHelper::is_item_enabled( 'structure.header_elements.social' ) ) {
            return;
        }

        if ( $profiles = theme_container()->get( Social::class )->get_social_profiles() ) {
            echo '<div class="header-social social-buttons">';
            echo $profiles;
            echo '</div>';
        }
    }

    /**
     * @return void
     */
    public function _output_header_html_1() {
        if ( ! CustomizerHelper::is_item_enabled( 'structure.header_elements.html_1' ) ) {
            return;
        }

        $content = $this->customizer->get_option( 'structure.html_1' );

        /**
         * @since 1.1
         */
        $content = apply_filters( 'wpcommunity/layout/header_html_1_content', $content );

        echo '<div class="header-html-1">';
        echo $content;
        echo '</div>';
    }

    /**
     * @return void
     */
    public function _output_header_html_2() {
        if ( ! CustomizerHelper::is_item_enabled( 'structure.header_elements.html_2' ) ) {
            return;
        }

        $content = $this->customizer->get_option( 'structure.html_2' );

        /**
         * @since 1.1
         */
        $content = apply_filters( 'wpcommunity/layout/header_html_2_content', $content );

        if ( $content ) {
            echo '<div class="header-html-2">';
            echo $content;
            echo '</div>';
        }
    }

    /**
     * @return void
     */
    public function _output_footer_blocks() {
        for ( $block = 1 ; $block <= 6 ; $block ++ ) {
            if ( CustomizerHelper::is_item_enabled( "structure.footer_elements.block_{$block}" ) ) {
                $content = (string) $this->customizer->get_option( "structure.footer_elements.block_{$block}_content" );

                /**
                 * @since 1.0
                 */
                $content = apply_filters( 'wpcommunity/footer-block/content', $content, $block );

                get_template_part( 'template-parts/elements/footer-block', null, compact( 'content', 'block' ) );
            }
        }
    }

    /**
     * @return void
     */
    public function _output_styles() {
        $css = new CssBuilder();
        $css->register_media( '(min-width:768px)', 10 );
        $css->register_media( '(min-width:1024px)', 20 );

        $this
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'structure.header_elements' ),
                $css
            )
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'structure.site_branding_elements' ),
                $css
            )
        ;

        // site branding
        $css->new_rule( '.site-branding img' )
            ->set_media( $this->device_media['tablet'] )
            ->add_property( 'max-width', $this->customizer->get_option( 'site_branding.logo_max_width' ) )
        ;
        $css->new_rule( '.site-branding img' )
            ->add_property( 'max-height', '3em' )
        ;
        $css->new_rule( '.site-branding img' )
            ->set_media( $this->device_media['tablet'] )
            ->add_property( 'max-height', $this->customizer->get_option( 'site_branding.logo_max_height' ) ?: 'none' )
        ;

        // todo move to PostCard
        //$this->post_card->append_styles($css);

        // single post
        $this
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post.content_elements' ),
                $css,
                '.single-post '
            )
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post.header_elements' ),
                $css,
                '.single-post '
            )->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post.header_right_elements' ),
                $css,
                '.single-post '
            )
        ;
        $this
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post.footer_elements' ),
                $css,
                '.single-post '
            )
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post.footer_right_elements' ),
                $css,
                '.single-post '
            )
        ;
        $css->new_rule( '.single-post .post-meta__right' )->add_property( 'order', 1100 );

        // post card
        $this
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post_card.content_elements' ),
                $css,
                '.post-cards '
            )
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post_card.header_elements' ),
                $css,
                '.post-cards '
            )->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post_card.header_right_elements' ),
                $css,
                '.post-cards '
            )
        ;
        $this
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post_card.footer_elements' ),
                $css,
                '.post-cards '
            )
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'post_card.footer_right_elements' ),
                $css,
                '.post-cards '
            )
        ;
        $css->new_rule( '.post-cards .post-meta__right' )->add_property( 'order', 1100 );

        $this->gather_opt_styles(
            CustomizerHelper::get_json_option_values( 'social_profiles.profiles' ),
            $css,
            '.header-social '
        );
        $this->gather_opt_styles(
            CustomizerHelper::get_json_option_values( 'social_share.services' ),
            $css,
            '.social-share '
        );

        $this
            ->gather_opt_styles(
                CustomizerHelper::get_json_option_values( 'structure.footer_elements' ),
                $css
            )
        ;

        // scroll to top

        $width  = $this->customizer->get_option( 'scroll_to_top.button_width' );
        $height = $this->customizer->get_option( 'scroll_to_top.button_height' );

        $properties = [
            'width'  => "{$width}px",
            'height' => "{$height}px",
            'bottom' => $this->customizer->get_option( 'scroll_to_top.indent_y' ) . 'px',
        ];

        $use_custom_colors = $this->customizer->get_option( 'scroll_to_top.use_custom_colors' );
        if ( $use_custom_colors ) {
            $properties['color']      = $this->customizer->get_option( 'scroll_to_top.color' );
            $properties['background'] = $this->customizer->get_option( 'scroll_to_top.background_color' );
        }
        if ( $this->customizer->get_option( 'scroll_to_top.position' ) === 'right' ) {
            $properties['right'] = $this->customizer->get_option( 'scroll_to_top.indent_x' ) . 'px';
        } else {
            $properties['left'] = $this->customizer->get_option( 'scroll_to_top.indent_x' ) . 'px';
        }
        $css->new_rule( '.scrolltop' )
            ->add_properties( $properties )
        ;
        $content = $this->customizer->get_option( 'scroll_to_top.icon' );
        $css->new_rule( '.scrolltop::before' )
            ->add_property( 'content', $content ? "'{$content}'" : 'none' )
            ->add_property( 'color', $use_custom_colors ? $this->customizer->get_option( 'scroll_to_top.color' ) : null )
        ;

        $css->pretty = defined( 'WP_DEBUG' ) && WP_DEBUG;

        do_action( 'wpcommunity/layout/css', $css );

        ?>
        <style id="wpcommunity-styles"><?php echo $css ?></style>
        <?php
    }

    /**
     * @param array      $option
     * @param CssBuilder $css
     * @param string     $selector_prefix
     *
     * @return $this
     */
    protected function gather_opt_styles( $option, CssBuilder $css, $selector_prefix = '' ) {
        $prev_items = [];
        foreach ( $this->device_media as $device => $media ) {
            if ( ! array_key_exists( $device, $option ) ) {
                continue;
            }

            foreach ( $option[ $device ] as $item ) {
                $selector = $selector_prefix . $item['selector'];

                $rule = $css->new_rule( $selector );
                $rule->set_media( $media );

                if ( array_key_exists( $selector, $prev_items ) ) {
                    $prev_item = $prev_items[ $selector ];

                    if ( $diff = array_diff_assoc( $item, $prev_item ) ) {
                        if ( array_key_exists( 'order', $diff ) ) {
                            $rule->add_property( 'order', $diff['order'] );
                        }
                        if ( array_key_exists( 'enabled', $diff ) ) {
                            $prop = ! empty( $item['display_prop'] ) ? $item['display_prop'] : 'block';
                            $rule->add_property( 'display', $diff['enabled'] ? $prop : 'none' );
                        }
                    }
                } else {
                    $rule
                        ->add_property( 'order', $item['order'] )
                        ->add_property( 'display', $item['enabled'] ? ( ! empty( $item['display_prop'] ) ? $item['display_prop'] : 'block' ) : 'none' )
                    ;
                }

                $this->hook_for_item( $item, $rule, $device, $media, $css );

                $prev_items[ $selector ] = $item;
            }
        }

        return $this;
    }

    /**
     * @param array{'name':string} $item
     * @param CssRule              $item_rule
     * @param string               $device
     * @param string               $media
     * @param CssBuilder           $css
     *
     * @return void
     */
    protected function hook_for_item( $item, $item_rule, $device, $media, $css ) {
        if ( $item['name'] === 'structure.header_elements.main_navigation' ) {
            $hamburger_rule = $css->new_rule( '.main-navigation--hamburger' );
            $hamburger_rule->set_media( $media );
            $hamburger_rule->add_property( 'order', $item_rule->get_property( 'order' ) );
//            if ( $device === 'desktop' ) {
//                $display_prop = ! empty( $item['display_prop'] ) ? $item['display_prop'] : 'block';
//                $hamburger_rule->add_property( 'display', 'none' );
//                $item_rule->add_property( 'display', $item['enabled'] ? $display_prop : 'none' );
//            } else {
//                $hamburger_rule->add_property( 'display', $item['enabled'] ? 'block' : 'none' );
//                $item_rule->add_property( 'display', 'none' );
//            }
        }

    }

    /**
     * @return void
     */
    public function _main_loop() {
        $loop_context = theme_container()->get( LoopContext::class );
        while ( have_posts() ) {
            the_post();
            get_template_part( 'template-parts/post-card' );
            $loop_context->increase_counter();

            do_action( 'wpcommunity/posts_loop/after_card' );
        }
        wp_reset_postdata();
    }

    public function _archive_loop() {
        $loop_context = theme_container()->get( LoopContext::class );
        while ( have_posts() ) {
            the_post();

            get_template_part( 'template-parts/post-card' );
            $loop_context->increase_counter();

            do_action( 'wpcommunity/posts_loop/after_card' );
        }

        wp_reset_postdata();
    }

    /**
     * @return void
     */
    public function _the_posts_pagination() {

        /**
         * @since 1.0
         */
        $output_pagination = apply_filters( 'wpcommunity/layout/output_pagination', true );

        if ( ! $output_pagination ) {
            return;
        }

        the_posts_pagination();
    }

    /**
     * @param array $args
     *
     * @return array
     */
    public function _set_post_navigation_args( $args ) {
        $args['next_text'] = '<svg width="18" height="18"><use xlink:href="#ico-vote-plus"></use></svg>';
        $args['prev_text'] = '<svg width="18" height="18"><use xlink:href="#ico-vote-minus"></use></svg>';

        return $args;
    }

    /**
     * @param string
     *
     * @return void
     */
    public function _output_social_share( $context ) {
        if ( $context !== 'single' ) {
            return;
        }
        $buttons = get_share_buttons();
        if ( ! $buttons ) {
            return;
        }
        ?>

        <div class="social-share social-buttons">
            <?php echo $buttons; ?>
        </div>

        <?php
    }

    /**
     * @param array $classes
     *
     * @return array
     */
    public function _set_body_classes( $classes ) {
        // Adds a class of hfeed to non-singular pages.
        if ( ! is_singular() ) {
            $classes[] = 'hfeed';
        }

        return $classes;
    }

    /**
     * @param array $classes
     *
     * @return array
     */
    public function _set_sidebar_body_classes( $classes ) {
        if ( is_singular() ) {

            // Сейчас пока что возможно отключать сайдбары только на страницах и записях
            // В будущем с появлением возможности отключить сайдбар и в других местах нужно доработать и этот метод

            $sidebar_1_hidden = is_sidebar_hidden( 'sidebar-1' );
            $sidebar_2_hidden = is_sidebar_hidden( 'sidebar-2' );


            if ( $sidebar_1_hidden && $sidebar_2_hidden ) {
                $classes[] = 'no-sidebar';
            } else if ( $sidebar_1_hidden xor $sidebar_2_hidden ) {
                $classes[] = 'sidebar-1';
                if ( $sidebar_1_hidden ) {
                    $classes[] = 'sidebar-right';
                } else {
                    $classes[] = 'sidebar-left';
                }
            } else {
                $classes[] = 'sidebar-1c2';
            }

            /**
             * @since 1.0
             */
            $content_full_width = apply_filters( 'wpcommunity/layout/content_full_width', false, $classes );

            if ( $content_full_width ) {
                $classes[] = 'full-width';
            }
        } else {
            $classes[] = 'sidebar-1c2';
        }

        return $classes;
    }
}
