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
    <?php $settings->render_header( $args['label'] ); ?>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'Settings', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'account.pro.enable_expire_notification', __( 'Enable Expire Notification', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header( __( 'If Account is not Pro', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'account.not_pro.create_posts', __( 'Allow to Create Post', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'account.not_pro.add_comments', __( 'Can add Comments', 'wpcommunity' ) ); ?>
</div>
