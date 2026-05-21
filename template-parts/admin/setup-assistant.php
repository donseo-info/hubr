<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Admin\Settings;
use WPShop\WPCommunity\Admin\SetupAssistant;
use function WPShop\WPCommunity\get_settings_url;
use function WPShop\WPCommunity\theme_container;

$settings  = theme_container()->get( Settings::class );
$assistant = theme_container()->get( SetupAssistant::class );

?>

<div class="wrap wpshop-settings-wrap">

    <div class="wpshop-settings-head">
        <div class="wpshop-settings-head__title">
            <?php echo THEME_NAME ?>
        </div>
        <?php /*
        <div class="wpshop-settings-head__upgrade">
            <a href="#" class="wpshop-settings-promo-link">
                Расширить до неограниченной<br>
                со скидкой 20% до 11.11
            </a>
        </div>
 */ ?>
        <div class="wpshop-settings-head__version">
            <?php echo THEME_VERSION ?>
        </div>
        <div class="wpshop-settings-head__support">
            <a href="<?php echo $settings->doc_link( 'doc' ) . '/setup-assistant' ?>" target="_blank" rel="noopener">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16">
                    <path d="M176.29 83.67C196.65 70.56 221.66 64 251.34 64c38.99 0 71.38 9.32 97.17 27.95 25.79 18.63 38.69 46.24 38.69 82.81 0 22.43-5.59 41.32-16.78 56.67-6.54 9.32-19.1 21.22-37.68 35.71l-18.32 14.23c-9.98 7.76-16.61 16.82-19.87 27.17-2.07 6.56-3.19 16.74-3.36 30.54h-70.13c1.03-29.15 3.79-49.3 8.26-60.43 4.47-11.13 16-23.94 34.57-38.43l18.84-14.75c6.19-4.66 11.18-9.75 14.96-15.27 6.88-9.49 10.32-19.93 10.32-31.31 0-13.11-3.83-25.06-11.49-35.84-7.66-10.78-21.64-16.17-41.95-16.17s-34.13 6.64-42.47 19.93c-8.35 13.29-12.52 27.09-12.52 41.41h-74.79c2.07-49.17 19.24-84.02 51.5-104.55ZM220 376h76v72h-76v-72Z" fill="currentColor"></path>
                </svg>
            </a>
        </div>
    </div>

    <div class="wpshop-settings-container wpshop-settings-container--thin">
        <div class="wpshop-settings-box wpshop-settings-box--large-padding">

            <div class="wpshop-settings-installer-header"><?php echo esc_html__( 'Installation and Setup Wizard', 'wpcommunity' ) ?></div>

            <div class="wpshop-settings-installer-steps">
                <?php foreach ( $assistant->get_steps() as $idx => $step ): ?>
                    <div class="wpshop-settings-installer-step js-wpshop-settings-installer-step<?php echo $idx == 0 ? ' active' : '' ?><?php echo $assistant->is_disabled( $idx ) ? ' disabled' : '' ?>"
                         data-target="step-<?php echo $idx + 1 ?>"
                         title="<?php echo $step['title'] ?>">
                        <span><?php echo $idx + 1 ?></span>
                    </div>
                <?php endforeach ?>
            </div>

            <div class="wpshop-settings-installer-panels js-wpshop-settings-installer-panels">
                <?php foreach ( $assistant->get_steps() as $idx => $step ): ?>
                    <div class="wpshop-settings-installer-panel js-wpshop-settings-installer-panel"
                         data-tab="step-<?php echo $idx + 1 ?>"
                        <?php echo $idx == 0 ? '' : 'style="display:none"' ?>>

                        <div class="wpshop-settings-progress-container-wrap js-wpshop-settings-installer-progress-container">
                            <?php get_template_part( "template-parts/admin/setup-assistant/{$step['template']}" ) ?>
                        </div>

                        <div class="wpshop-settings-installer-panel__results js-wpshop-settings-installer-result-container" style="display: none"></div>

                        <div class="wpshop-settings-container__footer" style="position: sticky">
                            <?php if ( $assistant->has_next( $idx ) ): ?>
                                <button type="submit" class="wpshop-settings-button js-wpshop-settings-installer-next"<?php disabled( $assistant->is_disabled( $idx + 1, false ) ) ?>>
                                    <?php echo __( 'Next', 'wpcommunity' ) ?>
                                </button>
                            <?php else: ?>
                                <a href="<?php echo get_settings_url() ?>" class="wpshop-settings-button"><?php echo __( 'Done', 'wpcommunity' ) ?></a>
                            <?php endif ?>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</div>
