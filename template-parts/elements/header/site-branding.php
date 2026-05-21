<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Allows to apply custom logic of wrapping of site branding elements in link
 *
 * [ru] Позволяет применить свою логику оборачивания в ссылку элементов заголовка сайта
 *
 * @since 1.1
 */
$use_home_link = apply_filters(
    'wpcommunity/site_branding/use_home_link',
    ! ( is_front_page() && is_home() ) || is_paged()
);

$use_h1 = ! $use_home_link;

?>
<div class="site-branding">
    <?php
    $custom_logo_id      = get_theme_mod( 'custom_logo' );
    $custom_logo_dark_id = get_theme_mod( 'custom_logo_dark' );

    if ( $custom_logo_id ) {
        [ $img_src ] = wp_get_attachment_image_src( $custom_logo_id, 'full' );

        $logo_img_light = sprintf(
            '<img src="%s" class="logo site-branding__logo%s">',
            esc_attr( $img_src ),
            $custom_logo_dark_id ? ' site-branding__logo--light' : '' // добавляем модификатор темы если есть второе лого
        );

        $logo_img_dark = '';
        if ( $custom_logo_dark_id ) {
            [ $img_src ] = wp_get_attachment_image_src( $custom_logo_dark_id, 'full' );

            $logo_img_dark = sprintf(
                '<img src="%s" class="logo site-branding__logo site-branding__logo--dark">',
                esc_attr( $img_src )
            );
        }

        $logo_html = $logo_img_light . $logo_img_dark;

        // оборачиваем в ссылку на главную, если лого выводится не на главной

        if ( $use_home_link ) {
            $logo_html = sprintf(
                '<a href="%s" class="logo-link site-branding__logo-link">%s</a>',
                esc_attr( home_url( '/' ) ),
                $logo_html
            );
        }

        /**
         * Allows you to change the logic for not wrapping the site header in the link if the logo has been wrapped
         *
         * [ru] Позволяет изменить логику отмены оборачивания заголовка сайта в ссылке, если логотип был обернут
         *
         * @since 1.1
         */
        $skip_wrap_title = apply_filters( 'wpcommunity/site_branding/skip_wrap_title', true );
        if ( $use_home_link && $skip_wrap_title ) {
            // skip show title as link if logo was wrapped
            $use_home_link = false;
        }

        echo $logo_html;
    }
    ?>

    <?php
    $site_name = get_bloginfo( 'name', 'display' );
    if ( $site_name || is_customize_preview() ):
        if ( $use_home_link ) : ?>
            <div class="site-title site-branding__title">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home"><?php echo $site_name ?></a>
            </div>
        <?php else : ?>
            <?php if ( $use_h1 ): ?>
                <h1 class="site-title site-branding__title"><span><?php echo $site_name ?></span></h1>
            <?php else: ?>
                <div class="site-title site-branding__title"><span><?php echo $site_name ?></span></div>
            <?php endif ?>
        <?php endif;
    endif;

    $site_description = get_bloginfo( 'description', 'display' );
    if ( $site_description || is_customize_preview() ) :
        ?>
        <p class="site-description site-branding__description"><?php echo $site_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?></p>
    <?php endif; ?>
</div><!-- .site-branding -->
