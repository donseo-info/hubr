<?php

/**
 * @version 1.0.1
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var $args
 */

$args = wp_parse_args( $args, [
    'type'          => '',
    'target'        => '',
    'is_subscribed' => get_current_user_id(),
    'is_logged_in'  => false,
] );
?>
<?php if ( $args['is_logged_in'] ): ?>
    <button class="btn js-subscribe-action"
            data-action="<?php echo $args['is_subscribed'] ? 'unsubscribe' : 'subscribe' ?>"
            data-type="<?php echo esc_attr( $args['type'] ) ?>"
            data-target="<?php echo esc_attr( $args['target'] ) ?>"
            data-toggle_text="<?php echo esc_attr( ! $args['is_subscribed'] ? __( 'Unsubscribe', 'wpcommunity' ) : __( 'Subscribe', 'wpcommunity' ) ) ?>">
        <?php echo $args['is_subscribed'] ? __( 'Unsubscribe', 'wpcommunity' ) : __( 'Subscribe', 'wpcommunity' ) ?>
    </button>
<?php else: ?>
    <button class="btn js-subscribe-action"
            data-tooltip="<?php echo esc_attr__( 'Login required', 'wpcommunity' ) ?>"
            data-tooltip-pos="bottom" disabled
            data-action="subscribe"
            data-type="<?php echo esc_attr( $args['type'] ) ?>"
            data-target="<?php echo esc_attr( $args['target'] ) ?>"
            data-toggle_text="<?php echo esc_attr( __( 'Unsubscribe', 'wpcommunity' ) ) ?>">
        <?php echo __( 'Subscribe', 'wpcommunity' ) ?>
    </button>
<?php endif ?>

