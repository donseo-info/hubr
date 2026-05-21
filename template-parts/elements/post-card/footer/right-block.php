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

<div class="post-meta__right">
    <?php

    do_action('wpcommunity/post_card/footer_rights');
    ?>
</div>
