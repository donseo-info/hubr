<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var array{'label':string} $args
 */

$settings = PluginContainer::get( Settings::class );
//$settings = theme_container()->get( Settings::class );

?>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'License', '{{text-domain}}' ),
        sprintf( __( 'To activate the plugin, enter the license key that you receive after payment in the letter or in <a href="%s" target="_blank" rel="noopener">personal account</a>.', '{{text-domain}}' ), 'https://wpshop.ru/dashboard' )
    ); ?>
</div>

<div class="wpshop-settings-license">
    <?php if ( $error = $settings->get_reg_option()['license_error'] ): ?>
        <div class="error-message">
            <?php echo esc_html( $error ) ?>
        </div>
    <?php endif ?>
    <form class="wpshop-settings-license__form" action="" method="post" name="registration">
        <?php $settings->render_reg_input() ?>
    </form>
</div>
