<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Admin\Settings;
use function WPShop\WPCommunity\theme_container;

$settings = theme_container()->get( Settings::class );


?>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'License', 'wpcommunity' ),
        sprintf( __( 'To activate the plugin, enter the license key that you receive after payment in the letter or in <a href="%s" target="_blank" rel="noopener">personal account</a>.', 'wpcommunity' ), 'https://wpshop.ru/dashboard' )
    ); ?>
</div>

<div class="wpshop-settings-license">
    <form class="wpshop-settings-license__form" action="" method="post" name="registration">
        <?php $settings->render_reg_input(); ?>
    </form>
</div>
