<?php

/**
 * @version 1.0
 */

if ( ! defined( 'WPINC' ) ) {
    die;
}

use WPShop\WPCommunity\Helper;
use function WPShop\WPCommunity\theme_container;

$helper = theme_container()->get( Helper::class );

?>
<div class="post-meta__date post-card__date">
    <?php echo $helper->beauty_date( $post->post_date ) ?>
</div>
