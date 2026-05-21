<?php

namespace WPShop\WPCommunity\Metaboxes;

use WP_Post;

class MetaboxPostElements {

    /**
     * @var string[]
     */
    protected $post_types = [ 'post', 'page' ];

    /**
     * @return void
     */
    public function init() {
        add_action( 'add_meta_boxes', [ $this, '_add_meta_boxes' ] );
        add_action( 'save_post', [ $this, '_save_meta_box' ], 10, 2 );
        add_action( 'save_post', [ $this, '_save_page_elements' ], 10, 2 );
    }

    /**
     * @return void
     */
    public function _add_meta_boxes() {
        add_meta_box(
            'meta_post_elements',
            __( 'Post Elements', 'wpcommunity' ),
            [ $this, '_render_meta_box' ],
            $this->post_types,
            'side',
            'default'
        );

        add_meta_box(
            'meta_page_elements',
            __( 'Page Elements', 'wpcommunity' ),
            [ $this, '_render_page_meta_box' ],
            'page',
            'side',
            'default'
        );
    }

    /**
     * @return array[]
     */
    protected function get_elements() {
        return [
            [
                'name'  => 'sidebar-1',
                'label' => __( 'Hide Sidebar 1', 'wpcommunity' ),
            ],
            [
                'name'  => 'sidebar-2',
                'label' => __( 'Hide Sidebar 2', 'wpcommunity' ),
            ],
        ];
    }

    /**
     * @return array[]
     */
    protected function get_page_elements() {
        return [
            [
                'name'  => 'header',
                'label' => __( 'Page Header', 'wpcommunity' ),
            ],
        ];
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    public function _render_meta_box( $post ) {
        $hide_elements = (array) ( get_post_meta( $post->ID, 'hide_elements', true ) ?: [] );

        $related_ids = get_post_meta( $post->ID, 'related_post_ids', true );
        $source_link = get_post_meta( $post->ID, 'source_link', true );
        ?>
        <table class="form-table">
            <tr>
                <td>
                    <?php foreach ( $this->get_elements() as $element ): ?>
                        <p>
                            <label>
                                <input type="checkbox"
                                       name="hide_elements[<?php echo esc_attr( $element['name'] ) ?>]"
                                       value="1"
                                    <?php checked( in_array( $element['name'], $hide_elements ) ) ?>>
                                <?php echo esc_html( $element['label'] ) ?>
                            </label>
                        </p>
                    <?php endforeach ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $id = uniqid( 'related_post_ids.' ) ?>"><?php echo esc_html__( 'Related Post Ids', 'wpcommunity' ) ?></label>
                    <input type="text" name="related_post_ids" id="<?php echo $id ?>" value="<?php echo esc_attr( $related_ids ) ?>">
                </td>
            </tr>
            <tr>
                <td>
                    <label for="<?php echo $id = uniqid( 'source_link.' ) ?>"><?php echo esc_html__( 'Source Link', 'wpcommunity' ) ?></label>
                    <input type="text" name="source_link" id="<?php echo $id ?>" value="<?php echo esc_attr( $source_link ) ?>" placeholder="https://...">
                    <p class="description"><?php echo esc_html__( 'If you need to provide a link to an external site as a source, fill in this field', 'wpcommunity' ) ?></p>
                </td>
            </tr>
        </table>
        <?php
    }

    /**
     * @param WP_Post $post
     *
     * @return void
     */
    public function _render_page_meta_box( $post ) {
        $elements      = $this->get_page_elements();
        $page_elements = (array) ( get_post_meta( $post->ID, 'page_elements', true ) ?: [] )
        ?>
        <table class="form-table">
            <tr>
                <td>
                    <?php foreach ( $elements as $element ): ?>
                        <p>
                            <label>
                                <input type="checkbox"
                                       name="page_elements[<?php echo esc_attr( $element['name'] ) ?>]"
                                       value="1"
                                    <?php checked( in_array( $element['name'], $page_elements ) ) ?>>
                                <?php echo esc_html( $element['label'] ) ?>
                            </label>
                        </p>
                    <?php endforeach ?>
                </td>
            </tr>
        </table>
        <?php
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

        $hide_elements = array_keys( $_POST['hide_elements'] ?? [] );

        $elements = $this->get_elements();
        $elements = array_map( function ( $element ) {
            return $element['name'];
        }, $elements );

        $to_update = array_intersect( $elements, $hide_elements );

        update_post_meta( $post_id, 'hide_elements', $to_update );

        $related_ids = wp_parse_id_list( $_POST['related_post_ids'] ?? '' );
        $related_ids = implode( ',', $related_ids );

        update_post_meta( $post_id, 'related_post_ids', $related_ids );

        if ( $url = filter_var( $_POST['source_link'] ?? '', FILTER_SANITIZE_URL ) ) {
            update_post_meta( $post_id, 'source_link', $url );
        } else {
            delete_post_meta( $post_id, 'source_link' );
        }
    }

    /**
     * @param int     $post_id
     * @param WP_Post $post
     *
     * @return void
     */
    public function _save_page_elements( $post_id, $post ) {
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

        if ( ! in_array( $post->post_type, [ 'page' ] ) ) {
            return;
        }

        $page_elements = array_keys( (array) ( $_POST['page_elements'] ?? [] ) );

        $elements = $this->get_page_elements();
        $elements = array_map( function ( $element ) {
            return $element['name'];
        }, $elements );

        $to_update = array_intersect( $elements, $page_elements );

        update_post_meta( $post_id, 'page_elements', $to_update );
    }
}
