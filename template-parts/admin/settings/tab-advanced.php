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
    <?php $settings->render_header( __( 'Advanced Settings', 'wpcommunity' ), null, $settings->doc_link( 'doc' ) . '/settings#advanced' ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php if ( ! $settings->detect_multi_lang_plugins() ): ?>
        <?php $settings->render_checkbox( 'advanced.use_localized_settings', __( 'Enable Localized Settings', 'wpcommunity' ), [ 'disabled' => (bool) $settings->get_locale() ] ); ?>
    <?php else: ?>
        <label class="wpshop-settings-form-label">
            <input type="checkbox" class="wpshop-settings-switch-box" disabled checked>
            <?php echo __( 'Enable Localized Settings', 'wpcommunity' ) ?>
        </label>
        <p class="description"><?php echo __( 'A multi-language plugin was detected, so the localized settings option is automatically enabled', 'wpcommunity' ) ?></p>
    <?php endif ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_input(
        'site.privacy_policy.link_text',
        __( 'Privacy Policy Link', 'wpcommunity' ),
        [],
        __( 'You can use the shortcode for <code>[privacy_policy_link]</code> to add a link on the privacy policy page', 'wpcommunity' )
    ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox( 'advanced.enable_microdata', __( 'Enable MicroData (schema.org)', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row">
    <?php $settings->render_checkbox(
        'advanced.disable_illegal_logins',
        __( 'Disable User Illegal Logins', 'wpcommunity' ),
        [],
        __( 'Prohibits the use of words such as \'admin\' and similar terms, as well as profanity, as usernames.', 'wpcommunity' )
    ); ?>
</div>

<div class="wpshop-settings-header">
    <?php $settings->render_header(
        __( 'Code', 'wpcommunity' ),
        __( 'Here you can specify the code of metrics, advertising or other necessary scripts', 'wpcommunity' )
    ) ?>
</div>

<div class="wpshop-settings-form-row wpshop-settings-form-row-codemiror js-html-editor-container">
    <?php $settings->render_textarea( 'advanced.code.head', esc_html__( 'Code before </head> tag', 'wpcommunity' ) ); ?>
</div>

<div class="wpshop-settings-form-row wpshop-settings-form-row-codemiror js-html-editor-container">
    <?php $settings->render_textarea( 'advanced.code.body', esc_html__( 'Code before </body> tag', 'wpcommunity' ) ); ?>
</div>

<script>
    jQuery(function ($) {
        document.addEventListener('wpshop_settings:show_tab_content', function (e) {
            if (e.detail.tab !== 'tab-advanced') {
                return;
            }

            $('.js-html-editor-container').find('textarea').each(function () {
                var $this = $(this);
                if ($this.data('_init_code_editor')) {
                    return;
                }

                $this.data('_init_code_editor', 1);
                wp.codeEditor.initialize($this, {type: 'text/html'});
            });

        });
    });
</script>
