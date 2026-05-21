<?php

namespace WPShop\WPCommunity\Features;

use WP_Post;

class MenuIcons {

    const META_FIELD  = 'wpcommunity_menu_icon';
    const INPUT_FIELD = 'wpcommunity_menu_icon';

    /**
     * @return void
     */
    public function init() {
        add_action( 'wp_nav_menu_item_custom_fields', [ $this, '_add_icon_field' ], 10, 4 );
        add_action( 'wp_update_nav_menu_item', [ $this, '_save_icon' ], 10, 3 );
//        add_action( 'load-nav-menus.php', function () {
//                get_template_part( 'template-parts/menu/icon-options-popup' );
//            if ( get_current_screen() && get_current_screen()->id === 'nav-menus' ) {
//            }
//        } );
        add_action( 'print_media_templates', function () {
            if ( is_admin() && get_current_screen() && get_current_screen()->id === 'nav-menus' ) {
                get_template_part( 'template-parts/admin/menu/icon-options-popup' );
            }
        } );
    }

    /**
     * @param string  $id
     * @param WP_Post $item
     * @param int     $depth
     * @param array   $args
     *
     * @return void
     */
    public function _add_icon_field( $id, $item, $depth, $args ) {
        ?>
        <div class="description-wide wpcommunity-menu-icon" style="display: flex">
            <p class="description submitbox">
                <a href="#" class="js-wpcommunity-menu-icon-select"><?php echo __( 'Set Icon', 'wpcommunity' ) ?></a>
            </p>
            <div class="wpcommunity-menu-icon__preview"></div>
            <div class="wpcommunity-menu-icon__actions">
                <!--                    <span class="dashicons dashicons-plus"></span>-->
                <!--                    <span class="dashicons dashicons-edit"></span>-->
                <span class="dashicons dashicons-trash"></span>
            </div>
        </div>

        <input type="hidden" name="<?php echo self::INPUT_FIELD ?>" value="<?php echo esc_attr( '' ) ?>">
        <?php
    }

    /**
     * @param string $menu_id
     * @param int    $menu_item_db_id
     * @param array  $args
     *
     * @return void
     */
    public function _save_icon( $menu_id, $menu_item_db_id, $args ) {
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            return;
        }

        $screen = get_current_screen();
        if ( ! $screen || 'nav-menus' !== $screen->id ) {
            return;
        }

        check_admin_referer( 'update-nav_menu', 'update-nav-menu-nonce' );

        if ( ! empty( $_POST[ self::INPUT_FIELD ] ) ) {
            $data = wp_unslash( $_POST[ self::INPUT_FIELD ] );
            update_post_meta( $menu_item_db_id, self::META_FIELD, $data );
        } else {
            delete_post_meta( $menu_item_db_id, self::META_FIELD );
        }
    }
}
