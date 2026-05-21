<?php

/**
 * @version 1.2
 */

use function WPShop\WPCommunity\_ob_get_content;
use function WPShop\WPCommunity\get_setting;

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var $args
 */

$site_url  = get_bloginfo( 'url' );
$site_name = get_bloginfo( 'name' );
?>

<p style="margin: 0 0 20px;">
    <?php echo __( 'You\'re registered on', 'wpcommunity' ) ?>
    <a href="<?php echo esc_attr( $site_url ) ?>"><?php echo esc_html( $site_name ) ?></a>
</p>

<?php get_template_part( 'template-parts/mail/_template', 'box', [
    'content' => _ob_get_content( function () use ( $args ) {
        $email    = $args['email'];
        $password = $args['password'];
        ?>

        <p style="margin: 0 0 16px;">
            <b>E-mail:</b> <?php echo esc_html( $email ) ?>
        </p>
        <p style="margin: 0;">
            <b><?php echo __( 'Password', 'wpcommunity' ) ?>: <?php echo esc_html( $password ) ?></b>
        </p>
        <?php
    } ),
] ); ?>

<p style="margin: 30px 0 20px;">
    <a href="<?php echo esc_attr( get_the_permalink( get_setting( 'page.profile' ) ) ) ?>">
        <?php echo __( 'Sing In', 'wpcommunity' ) ?>
    </a>
</p>
