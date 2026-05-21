<?php

/**
 * @version 1.2
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * @var array{'content':string, 'utm':string} $args
 */

$site_url  = get_bloginfo( 'url' );
$site_name = get_bloginfo( 'name' );
$year      = date( 'Y' );

$utm              = $args['utm'] ?? '';
$unsubscribe_link = $args['unsubscribe_link'] ?? '';

?>
<table bgcolor="#eff2f7" cellspacing="0" cellpadding="0" border="0" width="100%" style="border-collapse:collapse; color:#333; font-family: -apple-system,BlinkMacSystemFont,Roboto,Helvetica Neue,sans-serif; font-size: 16px; line-height: 1.5; background-color: #eff2f7; background-repeat:no-repeat; border-radius: 8px;">
    <tr>
        <td align="center" style="padding:20px 0 30px;">
            <table width="600px" cellspacing="0" cellpadding="0" border="0" align="center" bgcolor="#ffffff" style="border-radius: 8px;">
                <tr>
                    <td align="center" style="padding:15px 40px 12px;border-bottom: 1px solid #EFEEF3;font-size:24px;font-weight:800;">
                        <a href="<?php echo esc_attr( $site_url . ( $utm ? "?$utm" : '' ) ) ?>" target="_blank" style="color: #333;text-decoration: none;">
                            <span style="color:#333;text-decoration: none;"><?php echo esc_html( $site_name ) ?></span>
                        </a>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px 40px;">
                        <?php echo $args['content'] ?>

                    </td>
                </tr>
            </table>
            <table width="600px" cellspacing="0" cellpadding="0" border="0" align="center">
                <tr>
                    <td style="padding:15px 40px;font-size:13px;text-align: center">
                        <?php if ( $unsubscribe_link ): ?>
                        <p style="margin: 0 0 10px;">
                            <?php echo sprintf( __( 'You have received this email because you are registered on the website: %s.', 'wpcommunity' ), esc_html( $site_name ) ) ?>
                            <br>
                            <?php endif ?>
                            <a href="<?php echo esc_attr( $unsubscribe_link . ( $utm ? "?$utm" : '' ) ) ?>" target="_blank" style="color:#434d80;"><?php echo __( 'Unsubscribe', 'wpcommunity' ) ?></a>
                        </p>
                        <p style="margin:0;"><?php echo $year ?> &copy; Copyright, <?php echo esc_html( $site_name ) ?></p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
