<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Admin\Settings;
use function WPShop\WPCommunity\theme_container;

/**
 * @var array{'label':string} $args
 */

$settings = theme_container()->get( Settings::class );
?>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Karma', 'wpcommunity' ), null, $settings->doc_link( 'doc' ) . '/settings#karma' ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'karma.post_publish', __( 'Post Publish', 'wpcommunity' ), [ 'type' => 'number' ] ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'karma.comment_publish', __( 'Comment Publish', 'wpcommunity' ), [ 'type' => 'number' ] ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'karma.comment_spam', __( 'Comment Spam', 'wpcommunity' ), [ 'type' => 'number' ] ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'karma.post_vote_change', __( 'Post Vote', 'wpcommunity' ), [ 'type' => 'number' ] ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input( 'karma.comment_vote_change', __( 'Comment Vote', 'wpcommunity' ), [ 'type' => 'number' ] ); ?>
</div>
