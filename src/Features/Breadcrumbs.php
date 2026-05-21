<?php

namespace WPShop\WPCommunity\Features;

use Wpshop\Core2\BreadcrumbsBuilder;
use WPShop\WPCommunity\Admin\Settings;
use function WPShop\WPCommunity\_ob_get_content;
use function WPShop\WPCommunity\get_setting;
use function WPShop\WPCommunity\get_the_attributes;

class Breadcrumbs {

    /**
     * @var BreadcrumbsBuilder
     */
    protected $breadcrumbs_builder;

    /**
     * @var Settings
     */
    protected $settings;

    /**
     * @param BreadcrumbsBuilder $breadcrumbs_builder
     * @param Settings           $settings
     */
    public function __construct(
        BreadcrumbsBuilder $breadcrumbs_builder,
        Settings $settings
    ) {
        $this->breadcrumbs_builder = $breadcrumbs_builder;
        $this->settings            = $settings;
    }

    /**
     * @return void
     */
    public function init() {
        $this->breadcrumbs_builder
            ->set_home_text( $this->settings->get_value( 'breadcrumbs.home_title' ) )
            ->set_home_link( $this->settings->get_value( 'breadcrumbs.home_link' ) )
            ->set_show_paged( $this->settings->get_value( 'breadcrumbs.show_paged' ) )
            ->set_separator( '<span class="breadcrumbs-separator">' . $this->settings->get_value( 'breadcrumbs.separator' ) . '</span>' )
            ->set_paged_format( esc_html__( 'Page %s', 'wpcommunity' ) )
            ->set_archive_format( esc_html__( 'Archive for %s', 'wpcommunity' ) )
            ->set_page_search_text( esc_html__( 'Search', 'wpcommunity' ) )
            ->set_page_404_text( esc_html__( 'Not Found', 'wpcommunity' ) )
        ;

        $this->breadcrumbs_builder->set_item_render_fn( function ( $item, $idx, $count ) {

            /**
             * Allows to set additional content for breadcrumb item
             *
             * @since 1.0
             */
            $additional_content = apply_filters( 'wpcommunity/breadcrumbs/item_additional_content', '', $item, $idx, $count );

            return sprintf(
                '<div class="breadcrumbs-element" %1$s>%2$s%3$s</div>',
                get_the_attributes( 'breadcrumbs-element' ),
                ! empty( $item['link'] )
                    ? sprintf(
                    '<a href="%1$s" class="breadcrumbs-item__link" %3$s><span %4$s>%2$s</span></a>',
                    $item['link'],
                    $item['text'] ?? '',
                    get_the_attributes( 'breadcrumbs-item' ),
                    get_the_attributes( 'breadcrumbs-item-name' )
                )
                    : sprintf(
                    '<span class="breadcrumbs-item__text" %2$s>%1$s</span>',
                    $item['text'] ?? '',
                    get_the_attributes( 'breadcrumbs-item-name' )
                ),
                $additional_content
            );
        } );

        /**
         * Allows to set properties for BreadcrumbsBuilder
         *
         * @since 1.0
         */
        do_action( 'wpcommunity/breadcrumbs/init', $this->breadcrumbs_builder );

        add_filter( 'wpshop_core/breadcrumbs/user_title', function ( $display_name ) {
            return sprintf( esc_html__( 'Profile of %s', 'wpcommunity' ), $display_name );
        } );

        add_action( 'wpcommunity/main/before', [ $this, '_output_breadcrumbs' ] );
    }

    /**
     * @param string $context
     *
     * @return void
     */
    public function _output_breadcrumbs( $context = '' ) {
        if ( ! get_setting( 'breadcrumbs.enabled' ) ) {
            return;
        }

        $breadcrumbs = _ob_get_content( function () {
            $this->output_breadcrumbs();
        } );

        if ( $breadcrumbs ) {
            echo '<div class="breadcrumbs-container">';
            echo $breadcrumbs;
            echo '</div>';
        }
    }

    /**
     * @param array $args
     *
     * @return void
     */
    public function output_breadcrumbs( $args = [] ) {
        $use_plugins = $this->settings->get_value( 'breadcrumbs.use_plugins' );

        $args = wp_parse_args( $args, [
            'before' => $use_plugins ? '<div class="breadcrumbs">' : '<div class="breadcrumbs" ' . get_the_attributes( 'breadcrumbs' ) . '>',
            'after'  => '</div>',
        ] );

        /**
         * Allows to change output args
         *
         * @since 1.0
         */
        $args = apply_filters( 'wpcommunity/breadcrumbs/output_args', $args );

        if ( $use_plugins ) {

            if ( function_exists( 'yoast_breadcrumb' ) ) {
                $options = get_option( 'wpseo_titles' );
                if ( ! empty( $options['breadcrumbs-enable'] ) ) {
                    if ( $yoast_breadcrumbs = yoast_breadcrumb( '', '', false ) ) {
                        echo $args['before'];
                        echo $yoast_breadcrumbs;
                        echo $args['after'];
                    }
                }

                return;
            }

            if ( function_exists( 'rank_math_get_breadcrumbs' ) &&
                 \RankMath\Helper::get_settings( 'general.breadcrumbs' )
            ) {
                echo rank_math_get_breadcrumbs( [
                    'wrap_before' => $args['before'],
                    'wrap_after'  => $args['after'],
                ] );

                return;
            }
        }

        echo $this->breadcrumbs_builder->get_string( $args );
    }
}
