<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Membership;
use function WPShop\WPCommunity\theme_container;

$membership = theme_container()->get( Membership::class );
?>
<div class="post-meta__access">
    <?php if ( $membership->is_post_access() ) : ?>
        <span class="post-meta__access-public" data-tooltip="<?php _e( 'Public', 'wpcommunity' ) ?>" data-tooltip-pos="bottom">
                    <svg width="20" height="20"><use xlink:href="#ico-public"></use></svg>
                </span>
    <?php else : ?>

        <?php if ( $membership->is_user_post_access() ) : ?>
            <span class="post-meta__access-unlock" data-tooltip="<?php _e( 'Private', 'wpcommunity' ) ?>" data-tooltip-pos="bottom">
                        <svg width="16" height="20"><use xlink:href="#ico-unlock"></use></svg>
                    </span>
        <?php else : ?>
            <span class="post-meta__access-lock" data-tooltip="<?php _e( 'Private', 'wpcommunity' ) ?>" data-tooltip-pos="bottom">
                        <svg width="16" height="20"><use xlink:href="#ico-lock"></use></svg>
                    </span>
        <?php endif; ?>

    <?php endif; ?>
</div>
