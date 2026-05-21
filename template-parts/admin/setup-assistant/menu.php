<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\DefaultPages;
use function WPShop\WPCommunity\theme_container;

$settings      = theme_container()->get( Settings::class );
$default_pages = theme_container()->get( DefaultPages::class );
?>

<div class="wpshop-settings-header">
    <div class="wpshop-settings-header__title">
        <span><?php echo __( 'Create the Minimum Necessary Menu Items', 'wpcommunity' ) ?></span>
    </div>
    <div class="wpshop-settings-header__description">
        <p><?php echo __( 'Here you can create a top menu and a menu at the bottom of the sidebar.', 'wpcommunity' ) ?></p>
        <p><?php echo __( 'The menu at the top of the sidebar is created as a separate widget in the next step.', 'wpcommunity' ) ?></p>
    </div>
</div>

<form action="">
    <div class="wpshop-settings-header">
        <div class="wpshop-settings-header__title">
            <?php echo __( 'Top Menu Elements', 'wpcommunity' ) ?>
        </div>
    </div>

    <?php foreach ( $default_pages->get_pages() as $page_name => $page ):
        if ( ! in_array( $page_name, [ 'join', 'about', 'publish', 'profile' ] ) ) {
            continue;
        }
        ?>
        <div class="wpshop-settings-form-row">
            <label for="<?php echo $_id = uniqid( 'pages.' ) ?>" class="wpshop-settings-form-label">
                <input type="hidden" name="primary-menu[<?php echo $page_name ?>]" value="0">
                <input type="checkbox" class="wpshop-settings-switch-box" name="primary-menu[<?php echo $page_name ?>]" id="<?php echo $_id ?>" value="1" checked>
                <?php echo $page['title'] ?>
            </label>
        </div>
    <?php endforeach; ?>

    <div class="wpshop-settings-header">
        <div class="wpshop-settings-header__title">
            <?php echo __( 'Menu Elements for the Bottom of the Sidebar', 'wpcommunity' ) ?>
        </div>
    </div>

    <?php foreach ( $default_pages->get_pages() as $page_name => $page ):
        if ( ! in_array( $page_name, [ 'payment', 'offer', 'contacts' ] ) ) {
            continue;
        }
        ?>
        <div class="wpshop-settings-form-row">
            <label for="<?php echo $_id = uniqid( 'pages.' ) ?>" class="wpshop-settings-form-label">
                <input type="hidden" name="sidebar-bottom[<?php echo $page_name ?>]" value="0">
                <input type="checkbox" class="wpshop-settings-switch-box" name="sidebar-bottom[<?php echo $page_name ?>]" id="<?php echo $_id ?>" value="1" checked>
                <?php echo $page['title'] ?>
            </label>
        </div>
    <?php endforeach; ?>
</form>

<button type="button" class="wpshop-settings-button js-wpshop-settings-installer-action" data-action="create_menu">
    <?php echo __( 'Create Menu', 'wpcommunity' ) ?>
</button>
