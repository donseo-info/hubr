<?php

namespace WPShop\WPCommunity\Metaboxes;

use WP_Post;
use WPShop\WPCommunity\FrontendPublish;
use WPShop\WPCommunity\Post;
use function WPShop\WPCommunity\theme_container;

class MetaboxPostSettings {

    /**
     * @var string[]
     */
    protected $post_types = [ 'post' ];

    /**
     * @return void
     */
    public function init() {
        add_action( 'add_meta_boxes', [ $this, '_add_meta_boxes' ] );
        add_action( 'save_post', [ $this, '_save_meta_box' ], 10, 2 );
    }

    /**
     * @return void
     */
    public function _add_meta_boxes() {
        add_meta_box(
            'meta_post_settings',
            __( 'Post Format', 'wpcommunity' ),
            [ $this, '_render_meta_box' ],
            $this->post_types,
            'side',
            'default'
        );
    }

    /**
     * @param int     $post_id
     * @param WP_Post $post
     *
     * @return void
     */
    public function _save_meta_box( $post_id, $post ) {
        $post_type = get_post_type_object( $post->post_type );
        if ( ! current_user_can( $post_type->cap->edit_post, $post_id ) ) {
            return;
        }

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        if ( wp_is_post_autosave( $post_id ) ) {
            return;
        }

        if ( wp_is_post_revision( $post_id ) ) {
            return;
        }

        if ( ! in_array( $post->post_type, $this->post_types ) ) {
            return;
        }

        update_post_meta( $post_id, Post::POST_META_FORMAT, $_POST['post_format'] ?? 'post' );
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    public function _render_meta_box( $post ) {
        $formats        = theme_container()->get( FrontendPublish::class )->get_formats();
        $current_format = get_post_meta( $post->ID, Post::POST_META_FORMAT, true );
        ?>
        <table class="form-table">
            <tr>
                <th>
                    <label for="<?php echo $id = uniqid( 'post_format.' ) ?>"><?php echo __( 'Format', 'wpcommunity' ) ?></label>
                </th>
                <td>
                    <select name="post_format" id="<?php echo $id ?>">
                        <?php foreach ( $formats as $value => $label ): ?>
                            <option value="<?php echo esc_attr( $value ) ?>"<?php selected( $value, $current_format ) ?>><?php echo esc_html( $label ) ?></option>
                        <?php endforeach ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
}
