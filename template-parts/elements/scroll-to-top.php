<?php

/**
 * @version 1.1
 */


use WPShop\WPCommunity\Customizer\Customizer;
use function WPShop\WPCommunity\theme_container;

if ( ! defined( 'WPINC' ) ) {
    die;
}

$customizer = theme_container()->get( Customizer::class );

$content = '';
if ( ! $customizer->get_option( 'scroll_to_top.icon' ) ) {
    $content = $customizer->get_option( 'scroll_to_top.custom_icon' );
}

?>
<button class="scrolltop js-scrolltop"
        title="<?php echo esc_html_x( 'Scroll to Top', 'scrolltop_title', 'wpcommunity' ) ?>"
        data-mob="<?php echo $customizer->get_option( 'scroll_to_top.enabled_mobile' ) ? 'on' : 'off' ?>">
    <span><?php echo $content ?></span>
</button>
