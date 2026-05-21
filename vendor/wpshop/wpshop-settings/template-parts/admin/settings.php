<?php

/**
 * @version 1.0.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

$settings = PluginContainer::get( Settings::class );
//$settings = theme_container()->get( Settings::class );

?>

<div class="wrap wpshop-settings-wrap">
    <div class="wpshop-settings-head">
        <div class="wpshop-settings-head__title">
            {{Product Name}}
        </div>
        <?php if ( $settings->use_localized_settings() ): ?>
            <div class="wpshop-settings-head__locale">
                <?php echo __( 'Settings Scope', '{{text-domain}}' ) ?>:
                <?php
                $languages = get_available_languages();

                $settings->wp_dropdown_languages( [
                    'id'                       => 'wpsc-settings-locale',
                    'selected'                 => $_REQUEST['locale'] ?? '',
                    'languages'                => $languages,
                    'show_option_site_default' => true,
                    'explicit_option_en_us'    => true,
                ] );
                ?>
                <script>
                    jQuery(function ($) {
                        $(document).on('change', '#wpsc-settings-locale', function (e) {
                            var url = new URL(window.location.href);
                            var locale = $(this).val();
                            if (locale === 'site-default') {
                                url.searchParams.delete('locale');
                            } else {
                                url.searchParams.set('locale', locale);
                            }
                            window.location.href = url;
                        });
                    });
                </script>
            </div>
        <?php endif ?>
        <div class="wpshop-settings-head__version">
            <?php echo '{{plugin-version}}' ?>
        </div>
        <div class="wpshop-settings-head__support">
            <a href="#docs">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="16" height="16">
                    <path d="M176.29 83.67C196.65 70.56 221.66 64 251.34 64c38.99 0 71.38 9.32 97.17 27.95 25.79 18.63 38.69 46.24 38.69 82.81 0 22.43-5.59 41.32-16.78 56.67-6.54 9.32-19.1 21.22-37.68 35.71l-18.32 14.23c-9.98 7.76-16.61 16.82-19.87 27.17-2.07 6.56-3.19 16.74-3.36 30.54h-70.13c1.03-29.15 3.79-49.3 8.26-60.43 4.47-11.13 16-23.94 34.57-38.43l18.84-14.75c6.19-4.66 11.18-9.75 14.96-15.27 6.88-9.49 10.32-19.93 10.32-31.31 0-13.11-3.83-25.06-11.49-35.84-7.66-10.78-21.64-16.17-41.95-16.17s-34.13 6.64-42.47 19.93c-8.35 13.29-12.52 27.09-12.52 41.41h-74.79c2.07-49.17 19.24-84.02 51.5-104.55ZM220 376h76v72h-76v-72Z" fill="currentColor"></path>
                </svg>
            </a>
        </div>
    </div>

    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <?php settings_errors( 'abc_pagination_messages' ); ?>

    <div class="wpshop-settings-container">
        <div class="wpshop-settings-container__tabs">
            <ul class="wpshop-settings-tabs js-wpshop-settings-tabs">
                <?php
                $set_first_active = true;
                foreach ( $settings->get_tabs() as $tab ): ?>
                    <li data-tab="#<?php echo esc_attr( $tab['id'] ) ?>"<?php echo $set_first_active ? ' class="active"' : ''; ?>><?php
                        echo $settings->get_tab_icons()[ $tab['name'] ] ?? '';
                        echo '<span class="wpshop-settings-tab__label">' . esc_html( $tab['label'] ) . '</span>';
                        $set_first_active = false;
                        ?></li>
                <?php endforeach ?>
            </ul>
        </div>
        <div class="wpshop-settings-container__body">
            <div class="wpshop-settings-box">
                <?php $settings->wrap_form( function ( $settings ) {
                    $set_first_active = true;
                    foreach ( $settings->get_tabs() as $tab ): ?>
                        <div id="<?php echo $tab['id'] ?>" class="wpshop-settings-tab js-wpshop-settings-tab"<?php echo $set_first_active ? ' style="display:block"' : '' ?>>
                            <?php if ( current_user_can( 'manage_options' ) ): ?>
                                <?php Settings::get_template_part( 'admin/settings/tab', $tab['template_name'], [ 'label' => $tab['label'] ] ); ?>
                            <?php else: ?>
                                <p class="error-message"><?php echo __( 'Sorry, you are not allowed to perform actions on this section.', '{{text-domain}}' ) ?></p>
                            <?php endif ?>
                        </div>
                        <?php $set_first_active = false; endforeach;
                } ); ?>
            </div>
        </div>
        <div class="wpshop-settings-container__sidebar">
            <div class="wpshop-settings-sidebar-inner">

                <div class="wpshop-settings-box wpshop-settings-social-links">
                    <div><?php echo __( 'Our Pages:', '{{text-domain}}' ) ?></div>
                    <a href="https://t.me/wpshop" target="_blank" rel="noreferrer" class="wpshop-settings-social-links__item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="24" height="24">
                            <path d="M3.92 14.39c7.52-3.27 12.53-5.43 15.04-6.48 7.16-2.98 8.65-3.5 9.62-3.51.21 0 .69.05 1 .3.26.21.33.5.37.7.03.2.08.66.04 1.01-.39 4.08-2.07 13.97-2.92 18.54-.36 1.93-1.07 2.58-1.76 2.64-1.5.14-2.63-.99-4.09-1.94-2.27-1.49-3.55-2.41-5.75-3.87-2.55-1.68-.89-2.6.56-4.11.38-.39 6.98-6.4 7.1-6.94.02-.07.03-.32-.12-.46-.15-.13-.37-.09-.53-.05-.23.05-3.85 2.45-10.88 7.19-1.03.71-1.96 1.05-2.8 1.03-.92-.02-2.69-.52-4.01-.95-1.61-.52-2.9-.8-2.78-1.69.06-.46.7-.94 1.92-1.42Z" fill="currentColor"></path>
                        </svg>
                    </a>
                    <a href="https://vk.com/wpshop" target="_blank" rel="noreferrer" class="wpshop-settings-social-links__item">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="24" height="24">
                            <path d="M17.25 24.74C7.68 24.74 2.23 18.18 2 7.26h4.79c.16 8.01 3.69 11.4 6.49 12.1V7.26h4.51v6.91c2.76-.3 5.67-3.45 6.65-6.91h4.51c-.75 4.27-3.9 7.42-6.14 8.71 2.24 1.05 5.82 3.8 7.19 8.76h-4.97c-1.07-3.32-3.72-5.89-7.24-6.24v6.24h-.54Z" fill="currentColor"></path>
                        </svg>
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>
