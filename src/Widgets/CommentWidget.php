<?php

namespace WPShop\WPCommunity\Widgets;

use WP_Widget;
use WPShop\WPCommunity\Comments;
use function WPShop\WPCommunity\theme_container;

class CommentWidget extends WP_Widget {

    const WIDGET_ID = 'wpcommunity-comments';

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(
            self::WIDGET_ID,
            'WPCommunity: ' . __( 'Comments', 'wpcommunity' ),
            [
                'description' => __( 'Comments Widgets', 'wpcommunity' ),
            ]
        );
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
            //return;
        }

        $comments = get_comments( [
            'orderby' => 'comment_date',
            'order'   => 'DESC',
            'number'  => max( 1, min( $instance['count'], 1000 ) ),
            'status'  => 'approve',
        ] );

        if ( ! is_admin() ) {
            get_template_part( 'template-parts/widgets/comments', '', [
                'header'   => $instance['header'],
                'comments' => $comments,
            ] );
        }
    }

    /**
     * @param string|callable $cb_or_content
     *
     * @return void
     */
    protected function render( $cb_or_content ) {
        ?>
        <div class="widget widget-comments">
            <div class="widget-title widget-comments__header">
                <?php echo __( 'Comments', 'wpcommunity' ) ?>
            </div>
            <div class="widget-comments__content">
                <?php if ( is_callable( $cb_or_content ) ) {
                    call_user_func( $cb_or_content );
                } else {
                    echo '<p>' . $cb_or_content . '</p>';
                }
                ?>
                <a href="#comments" class="js-wpcommunity-goto-comments"><?php echo __( 'Go to Comments', 'wpcommunity' ) ?></a>
            </div>
        </div>
        <?php
    }

    /**
     * @param array $instance
     *
     * @return string|void
     */
    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, [
            'header'     => __( 'Comments', 'wpcommunity' ),
            'count'      => 5,
            'empty_text' => __( 'There are no comments yet.', 'wpcommunity' ),
        ] );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'header' ); ?>"><?php echo __( 'Header:', 'wpcommunity' ); ?></label>
            <input type="text" id="<?php echo $this->get_field_id( 'header' ); ?>" name="<?php echo $this->get_field_name( 'header' ); ?>" value="<?php echo esc_attr( $instance['header'] ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php echo __( 'Comments Count:', 'wpcommunity' ); ?></label>
            <input type="number" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo esc_attr( $instance['count'] ); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'empty_text' ); ?>"><?php echo __( 'Text if there are no comments:', 'wpcommunity' ); ?></label>
            <input type="text" id="<?php echo $this->get_field_id( 'empty_text' ); ?>" name="<?php echo $this->get_field_name( 'empty_text' ); ?>" value="<?php echo esc_attr( $instance['empty_text'] ); ?>">
        </p>
        <?php
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     *
     * @return array
     */
    public function update( $new_instance, $old_instance ) {
        $instance               = $old_instance;
        $instance['header']     = sanitize_text_field( $new_instance['header'] );
        $instance['count']      = absint( $new_instance['count'] );
        $instance['empty_text'] = sanitize_text_field( $new_instance['empty_text'] );

        return $instance;
    }
}
