<?php

namespace WPShop\WPCommunity\Widgets;

use WP_Widget;
use function WPShop\WPCommunity\get_setting;

/**
 * @see https://developer.wordpress.org/block-editor/how-to-guides/widgets/legacy-widget-block/
 */
class MenuWidget extends WP_Widget {

    const WIDGET_ID = 'wpcommunity-menu';

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            self::WIDGET_ID,
            'WPCommunity: ' . __( 'Left Menu', 'wpcommunity' ),
            [
                'description' => __( 'Widget for Left Menu of the Theme', 'wpcommunity' ),
            ]
        );
    }

    /**
     * @return array
     */
    public static function get_pages() {
        $pages = [
            'about'         => [
                'title' => __( 'About', 'wpcommunity' ),
                'icon'  => '<svg width="20" height="18"><use xlink:href="#ico-home"></use></svg>',
                'link'  => ( $page_id = get_setting( 'page.about' ) ) ? get_permalink( $page_id ) : '',

            ],
            'latest'        => [
                'title' => __( 'Latest', 'wpcommunity' ),
                'icon'  => '<svg width="20" height="20"><use xlink:href="#ico-time"></use></svg>',
                'link'  => get_bloginfo( 'url', 'display' ),
            ],
            'popular'       => [
                'title' => __( 'Popular', 'wpcommunity' ),
                'icon'  => '<svg width="18" height="20"><use xlink:href="#ico-fire"></use></svg>',
                'link'  => ( $page_id = get_setting( 'page.popular' ) ) ? get_permalink( $page_id ) : '',
            ],
            'subscriptions' => [
                'title' => __( 'Subscriptions', 'wpcommunity' ),
                'icon'  => '<svg width="18" height="19"><use xlink:href="#ico-user"></use></svg>',
                'link'  => ( $page_id = get_setting( 'page.subs' ) ) ? get_permalink( $page_id ) : '',
            ],
            'top'           => [
                'title' => __( 'Top', 'wpcommunity' ),
                'icon'  => '<svg width="20" height="20"><use xlink:href="#ico-top"></use></svg>',
                'link'  => ( $page_id = get_setting( 'page.top' ) ) ? get_permalink( $page_id ) : '',
            ],
            'bookmarks'     => [
                'title' => __( 'Bookmarks', 'wpcommunity' ),
                'icon'  => '<svg width="20" height="20"><use xlink:href="#ico-bookmark"></use></svg>',
                'link'  => ( $page_id = get_setting( 'page.bookmarks' ) ) ? get_permalink( $page_id ) : '',
            ],
            'gold'     => [
                'title' => __( 'Gold', 'wpcommunity' ),
                'icon'  => '<svg width="20" height="20"><use xlink:href="#ico-bookmark"></use></svg>',
                'link'  => ( $page_id = get_setting( 'page.gold' ) ) ? get_permalink( $page_id ) : '',
            ],
        ];

        /**
         * Allows to modify available pages for menu widget
         *
         * @since
         */
        $pages = apply_filters( 'wpcommunity/menu_widget/pages', $pages );

        return $pages;
    }

    /**
     * @param array                $items
     * @param array{'items':array} $instance
     *
     * @return mixed
     */
    protected function sort_by_instance( $items, $instance ) {
        $order = array_keys( $instance['items'] ?? [] );
        uksort( $items, function ( $key1, $key2 ) use ( $order ) {
            return array_search( $key1, $order ) > array_search( $key2, $order )
                ? 1
                : ( array_search( $key1, $order ) < array_search( $key2, $order ) ? - 1 : 0 );
        } );

        return $items;
    }

    /**
     * @param array $args
     * @param array $instance
     *
     * @return void
     */
    public function widget( $args, $instance ) {
        if ( ! isset( $instance['name'] ) ) {
            // Name is required, so display nothing if we don't have it.
//            return;
        }

        $items = static::get_pages();
        $items = $this->sort_by_instance( $items, $instance );

        $instance_titles = ! empty( $instance['titles'] ) ? json_decode( $instance['titles'], true ) : [];

        add_filter( 'wpcommunity/menu_widget/items', function ( $items ) use ( $instance_titles ) {
            foreach ( $items as $name => &$item ) {
                if ( array_key_exists( $name, $instance_titles ) ) {
                    $item['title'] = $instance_titles[ $name ];
                }
            }

            return $items;
        } );

        /**
         * @since 1.0
         */
        $items = apply_filters( 'wpcommunity/menu_widget/items', $items );

        get_template_part( 'template-parts/widgets/left-menu', '', [
            'widget_args' => $args,
            'items'       => $items,
            'enabled'     => function ( $key ) use ( $instance ) {
                return ! empty( $instance['items'][ $key ] );
            },
            'instance'    => $this,
        ] );
    }

    /**
     * @param string $key
     * @param array  $item
     *
     * @return bool
     */
    public function is_active( $key, $item ) {
        if ( array_key_exists( 'is_active', $item ) ) {
            if ( is_callable( $item['is_active'] ) ) {
                return call_user_func( $item['is_active'], $key, $item );
            }

            return $item['is_active'];
        }

        switch ( $key ) {
            case 'about':
                return is_page( get_setting( 'page.about' ) );
            case 'latest':
                return is_home();
            case 'popular':
                return is_page( get_setting( 'page.popular' ) );
            case 'subscriptions':
                return is_page( get_setting( 'page.subs' ) );
            case 'top':
                return is_page( get_setting( 'page.top' ) );
            case 'bookmarks':
                return is_page( get_setting( 'page.bookmarks' ) );
            default:
                break;
        }

        return false;
    }

    /**
     * @param array $instance
     *
     * @return string|void
     */
    public function form( $instance ) {
        wp_enqueue_script( 'wpcommunity-widget-menu' );

        $items = static::get_pages();
        $items = $this->sort_by_instance( $items, $instance );

        $titles          = [];
        $instance_titles = ! empty( $instance['titles'] ) ? json_decode( $instance['titles'], true ) : [];
        ?>
        <div class="wpcommunity-menu-widget js-wpcommunity-meni-widget">
            <div class="wpcommunity-menu-widget-list js-wpcommunity-meni-widget-sortable">
                <?php foreach ( $items as $name => $item ): ?>
                    <?php
                    $titles[ $name ] = $item['title'] ?? '';
                    $title           = array_key_exists( $name, $instance_titles ) ? $instance_titles[ $name ] : $item['title'];
                    ?>
                    <div class="wpcommunity-menu-widget-list__item js-wpcommunity-meni-widget-item">
                        <i class="dashicons dashicons-menu"></i>
                        <label>
                            <input type="hidden" name="<?php echo $this->get_field_name( "items[{$name}]" ) ?>" value="0">
                            <input type="checkbox" name="<?php echo $this->get_field_name( "items[{$name}]" ) ?>" value="1"<?php checked( ! empty( $instance['items'][ $name ] ) ) ?>>
                            <span class="js-wpcommunity-meni-widget-item-title"><?php echo esc_html( $title ) ?></span>
                        </label>
                        <input type="text" class="js-wpcommunity-meni-widget-item-edit--input" value="<?php echo esc_attr( $title ); ?>" data-page="<?php echo esc_attr( $name ) ?>" style="display: none">
                        <span class="dashicons dashicons-edit js-wpcommunity-meni-widget-item-edit"></span>
                        <span class="dashicons dashicons-no-alt js-wpcommunity-meni-widget-item-edit--cancel" style="display: none"></span>
                        <span class="dashicons dashicons-saved js-wpcommunity-meni-widget-item-edit--apply" style="display: none"></span>
                    </div>
                <?php endforeach ?>
            </div>
            <?php
            if ( $instance_titles ) {
                $instance_titles = array_intersect_key( $instance_titles, $titles );
                $instance_titles = array_merge( $titles, $instance_titles );
            } else {
                $instance_titles = $titles;
            }
            $instance_titles_json = json_encode( $instance_titles, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT );
            ?>
            <input type="hidden" class="js-wpcommunity-meni-widget-item-titles" name="<?php echo $this->get_field_name( 'titles' ) ?>" value="<?php echo esc_attr( $instance_titles_json ) ?>">
        </div>
        <?php
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance           = [];
        $instance['items']  = $new_instance['items'];
        $instance['titles'] = $new_instance['titles'] ?? '';

        return $instance;
    }
}
