<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}


?>

<div class="wpshop-settings-header">
    <div class="wpshop-settings-header__title">
        <span><?php echo __( 'Create the necessary pages', 'wpcommunity' ) ?></span>
    </div>
    <div class="wpshop-settings-header__description">
        <p><?php echo __( 'Profile, Subscriptions, Bookmarks, etc. pages are required for the theme to work properly.', 'wpcommunity' ) ?>
        <?php echo __( 'Click the "Create Pages" button to automatically create and setup pages.', 'wpcommunity' ) ?></p>
    </div>
</div>

<button type="button" class="wpshop-settings-button js-wpshop-settings-installer-action" data-action="create_pages">
    <?php echo __( 'Create Pages', 'wpcommunity' ) ?>
</button>
