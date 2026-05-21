<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\DefaultPages;
use WPShop\WPCommunity\Widgets\DarkModeSwitcherWidget;
use WPShop\WPCommunity\Widgets\MenuWidget;
use function WPShop\WPCommunity\theme_container;

$default_pages = theme_container()->get( DefaultPages::class );
$bottom_pages  = array_filter( $default_pages->get_pages(), function ( $page_name ) {
    return in_array( $page_name, [ 'payment', 'offer', 'contacts' ] );
}, ARRAY_FILTER_USE_KEY );

?>

<div class="wpshop-settings-header">
    <div class="wpshop-settings-header__title">
        <span><?php echo __( 'Create Default Widgets', 'wpcommunity' ) ?></span>
    </div>
    <div class="wpshop-settings-header__description">
        <p><?php echo __( 'The selected widgets will be automatically added to the sidebar on the left.', 'wpcommunity' ) ?></p>
    </div>
</div>

<form action="">
    <div class="wpshop-settings-header">
        <div class="wpshop-settings-header__title">
            <?php echo __( 'Menu Widget Elements', 'wpcommunity' ) ?>
        </div>
    </div>

    <input type="hidden" name="sidebar-top[<?php echo MenuWidget::WIDGET_ID ?>][_enable]" value="1">
    <?php foreach ( MenuWidget::get_pages() as $page_name => $page ): ?>
        <div class="wpshop-settings-form-row">
            <label for="<?php echo $_id = uniqid( 'widgets.' ) ?>" class="wpshop-settings-form-label">
                <input type="hidden" name="sidebar-top[<?php echo MenuWidget::WIDGET_ID ?>][<?php echo $page_name ?>]" value="0">
                <input type="checkbox" class="wpshop-settings-switch-box" name="sidebar-top[<?php echo MenuWidget::WIDGET_ID ?>][<?php echo $page_name ?>]" id="<?php echo $_id ?>" value="1" checked>
                <?php echo $page['title'] ?>
            </label>
        </div>
    <?php endforeach ?>

    <div class="wpshop-settings-header">
        <div class="wpshop-settings-header__title">
            <?php echo __( 'Widgets for the Bottom of the Sidebar', 'wpcommunity' ) ?>
        </div>
    </div>
    <div class="wpshop-settings-form-row">
        <label for="<?php echo $_id = uniqid( 'widgets.' ) ?>" class="wpshop-settings-form-label">
            <input type="hidden" name="sidebar-bottom[<?php echo DarkModeSwitcherWidget::WIDGET_ID ?>][_enable]" value="0">
            <input type="checkbox" class="wpshop-settings-switch-box" name="sidebar-bottom[<?php echo DarkModeSwitcherWidget::WIDGET_ID ?>][_enable]" id="<?php echo $_id ?>" value="1" checked>
            <?php echo __( 'Dark Mode Switcher', 'wpcommunity' ) ?>
        </label>
    </div>
    <div class="wpshop-settings-form-row">
        <label for="<?php echo $_id = uniqid( 'widgets.' ) ?>" class="wpshop-settings-form-label">
            <input type="hidden" name="sidebar-bottom[nav_menu][_enable]" value="0">
            <input type="checkbox" class="wpshop-settings-switch-box" name="sidebar-bottom[nav_menu][_enable]" id="<?php echo $_id ?>" value="1" checked>
            <?php echo __( 'Sidebar Bottom Menu', 'wpcommunity' ) ?>
        </label>
    </div>
</form>

<button type="button" class="wpshop-settings-button js-wpshop-settings-installer-action" data-action="create_widgets">
    <?php echo __( 'Create Widgets', 'wpcommunity' ) ?>
</button>
