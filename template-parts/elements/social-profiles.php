<?php

/**
 * @ver 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Social;
use function WPShop\WPCommunity\theme_container;

/**
 * @var array{'user_id':int} $args
 */

if ( ! array_key_exists( 'user_id', $args ) ) {
    return;
}

$social = theme_container()->get( Social::class );

if ( $website = $social->get_url( 'url', $args['user_id'] ) ): ?>
    <div class="profile-website">
        <?php echo esc_html__( 'Website', 'wpcommunity' ) ?>
        <a href="<?php echo esc_attr( $website ) ?>" rel="noopener" target="_blank"><?php echo esc_html( $website ) ?></a>
    </div>
<?php endif;
if ( $social_buttons = $social->get_social_profiles( $args['user_id'] ) ): ?>
    <div class="social-profiles">
        <?php echo $social_buttons ?>
    </div>
<?php endif;
