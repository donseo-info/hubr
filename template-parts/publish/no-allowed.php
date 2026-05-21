<?php

/**
 * @version 1.0
 */

use function WPShop\WPCommunity\get_setting;

?>

<div class="no-access">
    <div class="no-access__icon">😢</div>
    <?php _e( 'Sorry, you dont have permission to publish posts.', 'wpcommunity' ) ?>
    <a href="<?php the_permalink( get_setting( 'page.join' ) ) ?>"><?php _ex( 'Join', 'link', 'wpcommunity' ) ?>.</a>
</div>
